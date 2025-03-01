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

    // 🟢 ✅ ĐĂNG KÝ NGƯỜI DÙNG (Chat & Call)
    socket.on("register_user", (userId) => {
        users[userId] = socket.id;
        console.log(`✅ Registered user: ${userId} -> ${socket.id}`);
    });

    // 🟢 ✅ XỬ LÝ CHAT
    socket.on("send_message", (message) => {
        console.log("💬 Message received:", message);
        io.emit("receive_message", message); // Phát lại tin nhắn cho tất cả người dùng
    });

    // 🟢 ✅ BẮT ĐẦU CUỘC GỌI
    socket.on("start_call", ({ senderId, receiverId }) => {
        console.log(`📞 Start call from ${senderId} to ${receiverId}`);

        if (users[receiverId]) {
            console.log(`📢 Gửi incoming_call đến ${receiverId} (socketId: ${users[receiverId]})`);
            io.to(users[receiverId]).emit("incoming_call", { senderId });
        } else {
            console.log(`⚠️ User ${receiverId} không online.`);
        }
    });

    // 🟢 ✅ GỬI OFFER (WebRTC SDP)
    socket.on("offer", ({ offer, senderId, receiverId }) => {
        if (users[receiverId]) {
            console.log(`📡 Gửi offer từ ${senderId} đến ${receiverId}`);
            io.to(users[receiverId]).emit("offer", { offer, senderId });
        }
    });

    // 🟢 ✅ GỬI ANSWER (WebRTC SDP)
    socket.on("answer", ({ answer, receiverId }) => {
        if (users[receiverId]) {
            console.log(`✅ Gửi answer đến ${receiverId}`);
            io.to(users[receiverId]).emit("answer", { answer });
        }
    });

    // 🟢 ✅ GỬI ICE CANDIDATE (WebRTC)
    socket.on("ice_candidate", ({ candidate, receiverId }) => {
        if (users[receiverId]) {
            console.log(`❄ Gửi ICE Candidate đến ${receiverId}`);
            io.to(users[receiverId]).emit("ice_candidate", { candidate });
        }
    });

    // 🟢 ✅ KẾT THÚC CUỘC GỌI
    socket.on("end_call", ({ senderId, receiverId }) => {
        if (users[receiverId]) {
            console.log(`🚫 Cuộc gọi kết thúc từ ${senderId}`);
            io.to(users[receiverId]).emit("call_ended", { senderId });
        }
    });

    // 🛑 ✅ XÓA USER KHI NGẮT KẾT NỐI
    socket.on("disconnect", () => {
        console.log(`🔴 User disconnected: ${socket.id}`);

        for (let userId in users) {
            if (users[userId] === socket.id) {
                console.log(`🗑 Xóa user ${userId} khỏi danh sách.`);
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
