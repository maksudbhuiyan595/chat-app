<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function createGroup(Request $request)
    {
        $validated = $request->validate([
            'groupName' => 'required|string|max:255',
            'users' => 'required|array',
            'users.*' => 'exists:users,id'
        ]);

        // Create the group
        $group = new Group();
        $group->name = $validated['groupName'];
        $group->save();

        // Add users to the group
        foreach ($validated['users'] as $userId) {
            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $userId,
            ]);
        }

        // Fetch group members for response
        $groupMembers = GroupMember::with('user')->where('group_id', $group->id)->get();

        // Prepare response
        $response = [
            'groupName' => $group->name,
            'groupMembers' => $groupMembers->map(function ($groupUser) {
                return [
                    'user_id' => $groupUser->user->id,
                    'user_name' => $groupUser->user->name,
                ];
            })->all(),
            'status' => 'group created successfully!'
        ];

        // Return response
        return response()->json($response);
    }

    public function groupChat(Request $request,$id)
    {
        // dd($request->all());
        $users = User::where("id" , "!=",auth()->user()->id)->get();
        $groups = Group::all();

        return view("admin.page.chat.groupChat",compact("users","id","groups"));
    }

    public function sendGroupMessage(Request $request)
    {
        // c
        $message = GroupMessage::create([
            'group_id' => $request->group_id,
            'sender_id'=> $request->user_id,
            'message'  => $request->message,
        ]);


        // Broadcast the message to all group members using Socket.IO
        $messageData = [
            'group_id' => $message->group_id,
            'sender_id' => $message->sender_id,
            'message' => $message->message,
        ];

        // Use Laravel Echo and Socket.IO to broadcast the event
        // broadcast(new \App\Events\NewGroupMessage($messageData))->toOthers();

        return response()->json($messageData);
    }


public function uploadGroupImage(Request $request)
{
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $path = $file->store('group_images', 'public');

        $message = GroupMember::create([
            'group_id' => $request->group_id,
            'sender_id' => auth()->id(),
            'message' => $path,  // Save the image path as the message

        ]);

        $messageData = [
            'group_id' => $message->group_id,
            'sender_id' => $message->sender_id,
            'message' => asset('storage/' . $message->message),  // Send the URL of the image
            'type' => $message->type,
            'created_at' => $message->created_at->format('Y-m-d H:i:s'),
        ];

        // broadcast(new \App\Events\NewGroupImage($messageData))->toOthers();

        return response()->json($messageData);
    }

    return response()->json(['error' => 'No image uploaded'], 422);
}


}
