const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: {
        origin: "*",  // Cấu hình domain cho phép kết nối, nếu cần.
        methods: ["GET", "POST"]
    }
});

app.get('/', (req, res) => {
    res.send('Chat server is running');
});

// Xử lý kết nối và sự kiện gửi tin nhắn
io.on('connection', (socket) => {
    console.log('A user connected');

    // Lắng nghe sự kiện gửi tin nhắn từ client
    socket.on('send_message', (message) => {
        console.log('Message received:', message);
        // Phát lại tin nhắn cho tất cả người dùng đang kết nối
        io.emit('receive_message', message);
    });

    // Xử lý khi người dùng ngắt kết nối
    socket.on('disconnect', () => {
        console.log('User disconnected');
    });
});

// Lắng nghe trên cổng 3000
server.listen(3000, () => {
    console.log('Server running on http://localhost:3000');
});
