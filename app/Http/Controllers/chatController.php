<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class chatController extends Controller
{
    public function chat()
    {
        $users = User::where("id" , "!=",auth()->user()->id)->get();
        $messages = Message::all();
        $userList = User::all();
        $groups = Group::all();
        return view("admin.page.chat.chat",compact("users","messages","userList","groups"));
    }
    public function message($id)
    {
        $users = User::where("id" , "!=",auth()->user()->id)->get();
        $sender_id = auth()->user()->id;
        $messages = Message::where(function($query) use ($sender_id, $id) {
            $query->where('sender_id', $sender_id)
                ->where('receiver_id', $id);
        })->orWhere(function($query) use ($sender_id, $id) {
            $query->where('sender_id', $id)
                ->where('receiver_id', $sender_id);
        })->orderBy('created_at', 'asc')->get();
        $groups = Group::all();
        return view("admin.page.chat.chat",compact("users","id","messages","groups"));
    }

    public function sendMessage(Request $request){
        // dd($request->all());
        $message = Message::create([
            'sender_id' => auth()->user()->id,
            'receiver_id'=> $request->receiverId,
            'message'=> $request->message,
        ]);

        return response()->json([
            "message"=>$message,
            "userName"=>auth()->user()->name,
        ]);
    }
}
