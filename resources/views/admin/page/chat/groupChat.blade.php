@extends('admin.master')
<style>
    #video-call-container,
    #audio-call-container {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
    }

    .video-feed {
        width: 100%;
        max-width: 400px;
        margin-bottom: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .chat-messages {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .message.sent {
        background-color: #F4B5BA;
        color: #333;
        padding: 10px;
        border-radius: 7px;
        max-width: 75%;
        align-self: flex-end;
        text-align: right;
    }

    .message.received {
        background-color: #E0E0E0;
        color: #333;
        padding: 10px;
        border-radius: 7px;
        max-width: 75%;
        align-self: flex-start;
        text-align: left;
    }
</style>
@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="chat-with-all">Chat with all</h5>
            {{-- <div class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#creategroupModal">Create group</div> --}}
        </div>
        <ul class="nav nav-underline message-tap mb-4">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">All messages</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Unread</a>
            </li>
        </ul>
        <div class="chat-box-area">
            <div class="row">
                <div class="col-md-4">
                    <div class="chat-list border p-3">
                        <div class="search-container mb-3 position-relative">
                            <input type="search" id="user-search" class="form-control search-input"
                                placeholder="Search users">
                            <i class="bi bi-search search-icon position-absolute"
                                style="right: 10px; top: 50%; transform: translateY(-50%);"></i>
                        </div>
                        <input id="userId" type="hidden" value="{{ auth()->user()->id }}">
                        <input id="userName" type="hidden" value="{{ auth()->user()->name }}">

                        <div id="user-list-container" >

                            <input id="userList" type="hidden" name="users" value="{{ $userList ?? '' }} ">
                            @forelse ($users as $user)
                                <a href="{{ route('message', $user->id) }}">
                                    <li class="list-group-item d-flex align-items-center">
                                        <img src="" alt="" class="rounded-circle me-2"
                                            style="width: 40px; height: 40px;">
                                        <h6 class="mb-0">{{ $user->name }}</h6>&nbsp;&nbsp;&nbsp;<span>no active</span>
                                    </li>
                                </a>
                            @empty
                                <p>No Users</p>
                            @endforelse

                            <div id="group-list">
                                <hr>
                                <h5>Group List</h5>
                                <hr>
                                @forelse ($groups as $group)
                                <a href="{{ route('group.chat', $group->id) }}">
                                    <li class="list-group-item d-flex align-items-center">
                                        <img src="" alt="" class="rounded-circle me-2"
                                            style="width: 40px; height: 40px;">
                                        <h6 class="mb-0">{{ $group->name }}</h6>
                                    </li>
                                </a>
                            @empty
                                <p>No Groups</p>
                            @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="chat-box border p-3 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-start align-items-center mb-4">
                            <button id="audio-call-btn" class="btn btn-danger me-3">Audio Call</button>
                            <button id="video-call-btn" class="btn btn-warning">Video Call</button>
                        </div>
                        <div id="audio-call-container" class="d-none">
                            <h4>Audio Call</h4>
                            <div id="audio-status" class="mb-3">Connecting...</div>
                            <button id="end-audio-call-btn" class="btn btn-danger">End Call</button>
                        </div>
                        <!-- Video Call UI -->
                        <div id="video-call-container" class="d-none">
                            <h4>Video Call</h4>
                            <video id="local-video" autoplay muted class="video-feed"></video>
                            <video id="remote-video" autoplay class="video-feed"></video>
                            <div id="video-status" class="mb-3">Connecting...</div>
                            <button id="end-video-call-btn" class="btn btn-danger">End Call</button>
                        </div>
                        <hr>
                        <div id="chat-messages" class="chat-messages overflow-auto mb-3" style="height: 400px;">
                            {{-- @forelse ($messages as $message)
                                <p class="{{ $message->sender === Auth::user()->id ? 'sent' : 'received' }}">
                                    {{ $message->message }}
                                </p>
                            @empty
                                <p>No messages yet.</p>
                            @endforelse --}}
                        </div>

                        <form id="chat-input-form" class="chat-input-form d-flex align-items-center"
                            action="{{ route('send.group.message') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="receiver_id" name="group_id" value="{{ $id  }}">
                            <input type="hidden" id="user_id" name="user_id" value="{{ auth()->user()->id }}">
                            <input type="text" id="chat-input" name="message" class="form-control me-2" placeholder="Type your message">
                            <input type="file" id="image-upload-input" name="image" class="d-none" accept="image/*">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-camera image-icon me-2" id="trigger-image-upload"></i>
                                <button type="submit" class="send-button btn btn-primary">
                                    <i class="bi bi-send-fill"></i>
                                </button>
                            </div>
                        </form>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="image-upload-modal" tabindex="-1" aria-labelledby="image-upload-modalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="image-upload-modalLabel">Upload Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="image-upload-form" action="" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input class="dropify" data-height="100" type="file" id="image" name="image"
                                accept="image/*">
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="creategroupModal" tabindex="-1" aria-labelledby="creategroupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    {{-- <h5 class="modal-title" id="creategroupModalLabel">Create group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> --}}
                </div>
                <div class="modal-body">
                    <form id="createGroupForm">
                        @csrf
                        <div class="mb-3">
                            <input type="text" id="group-name" class="form-control" placeholder="Enter group Name" required>
                        </div>
                        <ul class="list-group" id="user-list">
                            @forelse ($users as $user)
                                <li class="list-group-item d-flex align-items-center" style="cursor: pointer">
                                    <input class="form-check-input me-2" name="users[]" type="checkbox" value="{{ $user->id }}" id="user_{{ $user->id }}">
                                    <img src="" alt="" class="rounded-circle me-2" style="width: 40px; height: 40px;">
                                    <h6 class="mb-0">{{ $user->name }}</h6>
                                </li>
                            @empty
                                <p class="text-muted">No users found</p>
                            @endforelse
                        </ul>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="submit-group-button" class="btn btn-primary">Create group</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script>

    const socket = io('http://localhost:3000');
        document.getElementById('trigger-image-upload').addEventListener('click', function() {
            document.getElementById('image-upload-input').click();
        });
    $('#chat-input-form').submit(function (e) {
    e.preventDefault();

    // const  = document.getElementById('receiver_id').value
    const formData = new FormData(this);
        // console.log(formData)
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            // Emit the message to Socket.IO
            socket.emit('newGroupMessage', response);
            // Append the message to the chat
            appendMessage(response);
        },
        error: function (error) {
            console.log(error);
        }
    });
});
$('#image-upload-form').submit(function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            // Emit the image to Socket.IO
            socket.emit('newGroupImage', response);
            // Append the image to the chat
            appendImage(response);
        },
        error: function (error) {
            console.log(error);
        }
    });
});
socket.on('newGroupMessage', function (message) {
    appendMessage(message);
});

socket.on('newGroupImage', function (image) {
    appendImage(image);
});

function appendMessage(message) {
    $('#chat-messages').append(`
        <div class="message">
            <strong>${message.sender_id}:</strong> ${message.message}
            <small>${message.created_at}</small>
        </div>
    `);
}

function appendImage(image) {
    $('#chat-messages').append(`
        <div class="message">
            <strong>${image.sender_id}:</strong>
            <img src="${image.message}" alt="Group Image" style="max-width: 200px;">
            <small>${image.created_at}</small>
        </div>
    `);
}


    </script>
@endsection
