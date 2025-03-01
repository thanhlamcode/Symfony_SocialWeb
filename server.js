const express = require("express");
const http = require("http");
const socketIo = require("socket.io");

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
        console.log("💬 Message received:", message);
        io.emit("receive_message", message); // Phát lại tin nhắn cho tất cả người dùng
    });

    socket.on("register_user", (userId) => {
        users[userId] = socket.id;
        console.log(`✅ Registered user: ${userId} -> ${socket.id}`);
    });

    socket.on("start_call", ({ senderId, receiverId, senderName }) => {
        if (users[receiverId]) {
            io.to(users[receiverId]).emit("incoming_call", { senderId, senderName });
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
        if (users[receiverId]) {
            io.to(users[receiverId]).emit("call_ended");
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

// Lắng nghe trên cổng 3000
server.listen(3000, () => {
    console.log("🚀 Chat & Call Server running on http://localhost:3000");
});
