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

    /* Container for chat messages */
    .chat-messages {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    /* Style for sent messages */
    .message.sent {
        background-color: #F4B5BA;
        color: #333;
        padding: 10px;
        border-radius: 7px;
        max-width: 75%;
        align-self: flex-end;
        /* Align to the right */
        text-align: right;
    }

    /* Style for received messages */
    .message.received {
        background-color: #E0E0E0;
        color: #333;
        padding: 10px;
        border-radius: 7px;
        max-width: 75%;
        align-self: flex-start;
        /* Align to the left */
        text-align: left;
    }
</style>
@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="chat-with-all">Chat with all</h5>
            <div class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#creategroupModal">Create group</div>
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
                <!-- Chat List Column -->
                <div class="col-md-4">
                    <div class="chat-list border p-3" id="group-list">
                        <div class="search-container mb-3 position-relative">
                            <input type="search" id="user-search" class="form-control search-input"
                                placeholder="Search users">
                            <i class="bi bi-search search-icon position-absolute"
                                style="right: 10px; top: 50%; transform: translateY(-50%);"></i>
                        </div>
                        <input id="userId" type="hidden" value="{{ auth()->user()->id }}">
                        <input id="userName" type="hidden" value="{{ auth()->user()->name }}">

                        <!-- Dynamic User List Container -->
                        <div id="user-list-container" class="list-group verflow-auto" style="max-height: 400px;">
                            @forelse ($users as $user)
                                <a href="{{ route('message', $user->id) }}">
                                    <li class="list-group-item d-flex align-items-center">
                                        <img src="" alt="" class="rounded-circle me-2"
                                            style="width: 40px; height: 40px;">
                                        <label class="form-check-label" for="user_{{ $user->id }}">
                                            <h6 class="mb-0">{{ $user->name }}</h6><span>no active</span>
                                        </label>
                                    </li>
                                    <hr>

                                @empty
                                    <p>No Users</p>
                            @endforelse

                            </a>
                        </div>
                    </div>
                </div>

                <!-- Chat Box Column -->
                <div class="col-md-8">
                    <div class="chat-box border p-3 d-flex flex-column justify-content-between">

                        <div class="d-flex justify-content-start align-items-center mb-4">
                            <button id="audio-call-btn" class="btn btn-primary me-3">Audio Call</button>
                            <button id="video-call-btn" class="btn btn-info">Video Call</button>
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
                            @forelse ($messages as $message)
                                <p class="{{ $message->sender === Auth::user()->id ? 'sent' : 'received' }}">
                                    {{ $message->message }}
                                </p>
                            @empty
                                <p>No messages yet.</p>
                            @endforelse
                        </div>


                        <form id="chat-input-form" class="chat-input-form d-flex align-items-center"
                            action="{{ route('send.message') }}" method="POST">
                            @csrf
                            <input type="hidden" id="receiver_id" name="reciverId" value="{{ $id ?? '' }}">
                            <input type="text" id="chat-input" name="message" class="form-control me-2"
                                placeholder="Type your message">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-camera image-icon me-2" data-bs-toggle="modal"
                                    data-bs-target="#image-upload-modal"></i>
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

    <!-- Image Upload Modal -->
    {{-- <div class="modal fade" id="image-upload-modal" tabindex="-1" aria-labelledby="image-upload-modalLabel"
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
<!-- form Modal -->
<div class="modal fade" id="creategroupModal" tabindex="-1" aria-labelledby="creategroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="creategroupModalLabel">Create group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="create-group-form">
                    @csrf
                    <div class="mb-3">
                        <input type="text" id="group-name" name="group_name" class="form-control"
                            placeholder="Enter group Name" required>
                    </div>
                    <div class="mb-3">
                        <input type="search" id="search-users" class="form-control" placeholder="Search users">
                    </div>
                    <ul class="list-group" id="user-list">
                        @forelse ($connectedUsers as $user)
                            <li class="list-group-item d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" value="{{ $user->user->id }}"
                                    id="user_{{ $user->user->id }}">
                                <img src="{{ asset('/avatars/man.png') }}" alt="{{ $user->user->name }}"
                                    class="rounded-circle me-2" style="width: 40px; height: 40px;">
                                <label class="form-check-label" for="user_{{ $user->user->id }}">
                                    <h6 class="mb-0">{{ $user->user->name }}</h6>
                                </label>
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
</div> --}}


    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script>
        const socket = io('http://localhost:3000');

        const userId = document.getElementById('userId').value
        const userName = document.getElementById('userName').value
        // console.log(userId,userName)

        // let user = {
        //     userId: userId,
        //     userName: userName
        // }
        // console.log(user)
        // socket.emit('connected user', user)
        // socket.on('update users', (users) => {
        //     console.log(users)
        //     const userListContainer = document.getElementById('user-list-container')
        //     userListContainer.innerHTML = '';

        //     // Append new user list items
        //     users.forEach(user => {
        //         const userItem = document.createElement('div');
        //         userItem.className = 'list-group-item';
        //         userItem.textContent = user.userName;
        //         userListContainer.appendChild(userItem);
        //     });
        // });



        document.getElementById('chat-input-form').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            const message = document.getElementById('chat-input').value;
            const receiverId = document.getElementById('receiver_id').value;

            $.ajax({
                url: $('#chat-input-form').attr('action'), // Get the form action URL
                method: 'POST',
                data: {
                    message: message,
                    receiverId: receiverId,
                    _token: $('meta[name="csrf-token"]').attr('content') // Laravel CSRF token
                },
                success: function(response) {
                    socket.emit('chat message', message);

                },
                error: function(xhr) {
                    console.log(xhr);
                }
            });

            document.getElementById('chat-input').value = '';
        });


        socket.on('chat message', function(message) {
            const messageElement = document.createElement('div');
            messageElement.textContent = message
            document.getElementById('chat-messages').appendChild(messageElement);
        });



        // Example usage: Load chat data when a user is selected
        $(document).on('click', '.list-group-item', function() {
            const receiverId = $(this).data('receiver-id');
            loadChatData(receiverId);
        });


        // audio call Logic
        //------------------------

        document.getElementById('audio-call-btn').addEventListener('click', function() {
            const audioCallContainer = document.getElementById('audio-call-container');
            const audioStatus = document.getElementById('audio-status');

            audioCallContainer.classList.remove('d-none');
            audioStatus.textContent = "Connecting...";

            const peerConnection = new RTCPeerConnection();
            navigator.mediaDevices.getUserMedia({
                audio: true
            }).then(stream => {
                peerConnection.addTrack(stream.getTracks()[0], stream);

                peerConnection.onicecandidate = function(event) {
                    if (event.candidate) {
                        socket.emit('audio candidate', event.candidate);
                    }
                };

                peerConnection.createOffer().then(offer => {
                    peerConnection.setLocalDescription(offer);
                    socket.emit('audio offer', offer);
                });

                socket.on('audio answer', answer => {
                    peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
                    audioStatus.textContent = "Call Connected";
                });

                socket.on('audio candidate', candidate => {
                    peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                });
            });

            document.getElementById('end-audio-call-btn').addEventListener('click', function() {
                peerConnection.close();
                audioCallContainer.classList.add('d-none');
                audioStatus.textContent = "Call Ended";
            });
        });

        socket.on('audio offer', function(offer) {
            const peerConnection = new RTCPeerConnection();
            peerConnection.setRemoteDescription(new RTCSessionDescription(offer));

            navigator.mediaDevices.getUserMedia({
                audio: true
            }).then(stream => {
                peerConnection.addTrack(stream.getTracks()[0], stream);

                peerConnection.createAnswer().then(answer => {
                    peerConnection.setLocalDescription(answer);
                    socket.emit('audio answer', answer);
                });

                peerConnection.onicecandidate = function(event) {
                    if (event.candidate) {
                        socket.emit('audio candidate', event.candidate);
                    }
                };

                socket.on('audio candidate', function(candidate) {
                    peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                });
            });
        });

        //video call logic
        //-------------------
        document.getElementById('video-call-btn').addEventListener('click', function() {
            const videoCallContainer = document.getElementById('video-call-container');
            const localVideo = document.getElementById('local-video');
            const remoteVideo = document.getElementById('remote-video');
            const videoStatus = document.getElementById('video-status');

            videoCallContainer.classList.remove('d-none');
            videoStatus.textContent = "Connecting...";

            const peerConnection = new RTCPeerConnection();
            navigator.mediaDevices.getUserMedia({
                video: true,
                audio: true
            }).then(stream => {
                localVideo.srcObject = stream;
                stream.getTracks().forEach(track => peerConnection.addTrack(track, stream));

                peerConnection.onicecandidate = function(event) {
                    if (event.candidate) {
                        socket.emit('video candidate', event.candidate);
                    }
                };

                peerConnection.ontrack = function(event) {
                    remoteVideo.srcObject = event.streams[0];
                };

                peerConnection.createOffer().then(offer => {
                    peerConnection.setLocalDescription(offer);
                    socket.emit('video offer', offer);
                });

                socket.on('video answer', answer => {
                    peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
                    videoStatus.textContent = "Call Connected";
                });

                socket.on('video candidate', candidate => {
                    peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                });
            });

            document.getElementById('end-video-call-btn').addEventListener('click', function() {
                peerConnection.close();
                videoCallContainer.classList.add('d-none');
                videoStatus.textContent = "Call Ended";
            });
        });

        socket.on('video offer', function(offer) {
            const peerConnection = new RTCPeerConnection();
            peerConnection.setRemoteDescription(new RTCSessionDescription(offer));

            navigator.mediaDevices.getUserMedia({
                video: true,
                audio: true
            }).then(stream => {
                const localVideo = document.getElementById('local-video');
                localVideo.srcObject = stream;
                stream.getTracks().forEach(track => peerConnection.addTrack(track, stream));

                peerConnection.createAnswer().then(answer => {
                    peerConnection.setLocalDescription(answer);
                    socket.emit('video answer', answer);
                });

                peerConnection.onicecandidate = function(event) {
                    if (event.candidate) {
                        socket.emit('video candidate', event.candidate);
                    }
                };

                peerConnection.ontrack = function(event) {
                    const remoteVideo = document.getElementById('remote-video');
                    remoteVideo.srcObject = event.streams[0];
                };

                socket.on('video candidate', function(candidate) {
                    peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                });
            });
        });
    </script>
@endsection
