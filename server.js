const express = require("express");
const http = require("http");
const socketIo = require("socket.io");
require("dotenv").config(); // Load biến môi trường từ .env

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: { origin: "*", methods: ["GET", "POST"] }
});

let users = {}; // Lưu userId và socketId

app.get("/", (req, res) => {
    res.send("Chat & Call server is running");
});

// Khi user kết nối WebSocket
io.on("connection", (socket) => {
    console.log(`🔵 User connected: ${socket.id}`);

    // 🟢 ✅ XỬ LÝ CHAT
    socket.on("send_message", (message) => {
        console.log(`💬 Tin nhắn từ ${message.senderId} đến ${message.receiverId}: ${message.text}`);

        // Gửi tin nhắn đến người nhận (chỉ nếu họ đang online)
        if (users[message.receiverId]) {
            io.to(users[message.receiverId]).emit("receive_message", message);
        }

        // Cũng gửi tin nhắn đến chính người gửi để cập nhật giao diện
        if (users[message.senderId]) {
            io.to(users[message.senderId]).emit("receive_message", message);
        }
    });


    socket.on("register_user", (userId) => {
        users[userId] = socket.id;
        console.log(`✅ Registered user: ${userId} -> ${socket.id}`);
    });

    socket.on("start_call", ({ senderId, receiverId, senderName, avatarSender }) => {
        console.log(`📞 Start call from ${senderId} to ${receiverId}`);

        if (users[receiverId]) {
            console.log(`📢 Gửi "incoming_call" tới ${receiverId} (socketId: ${users[receiverId]})`);
            io.to(users[receiverId]).emit("incoming_call", { senderId, senderName , avatarSender});
        } else {
            console.log(`⚠️ User ${receiverId} không online.`);
        }
    });

    socket.on("offer", ({ offer, senderId, receiverId }) => {
        if (users[receiverId]) {
            io.to(users[receiverId]).emit("offer", { offer, senderId });
        }
    });

    socket.on("answer", ({ answer, receiverId }) => {
        if (users[receiverId]) {
            io.to(users[receiverId]).emit("answer", { answer });
        }
    });

    socket.on("ice_candidate", ({ candidate, receiverId }) => {
        if (users[receiverId]) {
            io.to(users[receiverId]).emit("ice_candidate", { candidate });
        }
    });

    socket.on("end_call", ({ senderId, receiverId }) => {
        console.log(`🚫 Cuộc gọi từ ${senderId} đến ${receiverId} đã kết thúc.`);

        if (users[receiverId]) {
            io.to(users[receiverId]).emit("call_ended");
        }

        if (users[senderId]) {
            io.to(users[senderId]).emit("call_ended");
        }
    });

    socket.on("disconnect", () => {
        for (let userId in users) {
            if (users[userId] === socket.id) {
                delete users[userId];
                break;
            }
        }
    });
});

// Sử dụng PORT từ Render hoặc mặc định là 3000 khi chạy local
const PORT = process.env.PORT || 3000;

// Lắng nghe trên cổng 3000
server.listen(PORT, () => {
    console.log(`🚀 Chat & Call Server running on http://localhost:${PORT}`);
});