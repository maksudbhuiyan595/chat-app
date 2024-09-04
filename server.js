import express from 'express';
import http from 'http';
import { Server } from 'socket.io';

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});
let connectedUser =[];
io.on('connection', (socket) => {
    console.log('A user connected:', socket.id);
    socket.on('connected user', (userId) => {
            connectedUser.push(userId)
        io.emit('update users', connectedUser);
    });


    socket.on('audio offer', (offer) => {
        socket.broadcast.emit('audio offer', offer);
    });

    socket.on('audio answer', (answer) => {
        socket.broadcast.emit('audio answer', answer);
    });

    socket.on('audio candidate', (candidate) => {
        socket.broadcast.emit('audio candidate', candidate);
    });

    socket.on('video offer', (offer) => {
        socket.broadcast.emit('video offer', offer);
    });

    socket.on('video answer', (answer) => {
        socket.broadcast.emit('video answer', answer);
    });

    socket.on('video candidate', (candidate) => {
        socket.broadcast.emit('video candidate', candidate);
    });

    socket.on('chat message', (message) => {
        console.log('Message received:', message);
        io.emit('chat message', message);
    });

    socket.on('creategroup', (data) => {
        // Emit the newRoom event to all clients
        io.emit('newgroup', data);
    });

    socket.on('joinGroup', (groupId) => {
        socket.join(`group_${groupId}`);
        console.log(`User joined group: group_${groupId}`);
    });

    socket.on('sendMessage', (messageData) => {
        io.to(`group_${messageData.group_id}`).emit('receiveMessage', messageData);
    });

    socket.on('disconnect', () => {
        io.emit('update users',connectedUser);
    });
});

server.listen(3000, () => {
    console.log('server running at http://localhost:3000');
  });
