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
let users = [];
io.on('connection', (socket) => {
    console.log('A user connected:', socket.id);

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

    socket.on('connected user', (user) => {
        // Check if the user with the same userId already exists
        if (!users.some(u => u.userId === user.userId)) {
            // Add the user to the array if it's not already there
            users.push(user);
        }

        // Emit the updated list of users to all connected clients
        io.emit('update users', users);
    });

    socket.on('chat message', (message) => {
        console.log('Message received:', message);
        io.emit('chat message', message);
    });

    ocket.on('disconnect', () => {
        // Remove the disconnected user from the array
        users = users.filter(u => u.userId !== socket.userId);

        // Emit the updated list of users
        io.emit('update users', users);
    });
});

server.listen(3000, () => {
    console.log('server running at http://localhost:3000');
  });
