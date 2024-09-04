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
                    <h5 class="modal-title" id="creategroupModalLabel">Create group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

    document.getElementById('submit-group-button').addEventListener('click', function() {
    const groupName = document.getElementById('group-name').value.trim();
    const selectedUsers = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
        .map(cb => cb.value);

    if (!groupName) {
        alert('Please enter a group name.');
        return;
    }

    if (selectedUsers.length === 0) {
        alert('Please select at least one user.');
        return;
    }

    // Send AJAX request to create the group
    fetch('{{ route('group') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                groupName: groupName,
                users: selectedUsers,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                // Emit socket event with group creation details
                socket.emit('newgroup', {
                    groupMembers: data.groupMembers,
                    groupName: data.groupName
                });

                // Close the modal and show success message
                $('#creategroupModal').modal('hide');
                alert(data.status);

                // Update the UI with new group information
                updateGroupList(data.groupName, data.groupMembers);
            } else {
                alert('Failed to create group. Please try again.');
            }
        })
        .catch(error => alert('An error occurred. Please try again.'));
});

// Function to update group list in the UI
function updateGroupList(groupName, groupMembers) {
    const groupList = document.getElementById('group-list');

    // Create a new list item for the group
    const groupItem = document.createElement('div');
    groupItem.classList.add('user-list');

    groupItem.innerHTML = `
    <a href="">
        <li class="list-group-item d-flex align-items-center">
            <img src="" alt="" class="rounded-circle me-2"
                style="width: 40px; height: 40px;">
            <h6 class="mb-0">${groupName}</h6>
        </li>
        </a>

    `;



    // Append the new group to the group list
    groupList.appendChild(groupItem);
}

// Socket.IO event for new group
socket.on('newgroup', function(data) {
    console.log('New group created:', data);
    // Update the UI with the new group information
    updateGroupList(data.groupName, data.groupMembers);
});


        const userId = document.getElementById('userId').value
        const userName = document.getElementById('userName').value
        const userList = document.getElementById('userList').value.split(',');
        socket.emit('connected user', userId);

        socket.on('update users', (connectedUser) => {
            connectedUser.forEach(user => {
                // console.log(user)
            })
        });

        document.getElementById('chat-input-form').addEventListener('submit', function(e) {
            e.preventDefault();

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
            // loadChatData(receiverId);
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
