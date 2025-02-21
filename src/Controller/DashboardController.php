<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function dashboard(): Response
    {
        $user = $this->getUser(); // Lấy thông tin user đang đăng nhập

        // Danh sách chat (dữ liệu giả lập)
        $chatList = [
            [
                'name' => 'Nguyễn Văn A',
                'avatar' => '/images/avatar1.png',
                'last_message' => 'Bạn khỏe không?',
                'time' => '10:30 AM'
            ],
            [
                'name' => 'Trần Thị B',
                'avatar' => '/images/avatar2.png',
                'last_message' => 'Mai gặp nhau nhé!',
                'time' => '09:15 AM'
            ],
            [
                'name' => 'Lê Minh C',
                'avatar' => '/images/avatar3.png',
                'last_message' => 'Haha, đúng rồi đó!',
                'time' => 'Hôm qua'
            ]
        ];

        // Dữ liệu cuộc trò chuyện hiện tại
        $currentChat = [
            'name' => 'Nguyễn Văn A',
            'avatar' => '/images/avatar1.png'
        ];

        // Tin nhắn mẫu
        $messages = [
            ['sender' => $user, 'text' => 'Chào bạn, lâu rồi không gặp!'],
            ['sender' => 'Nguyễn Văn A', 'text' => 'Đúng rồi đó, dạo này sao rồi?'],
            ['sender' => $user, 'text' => 'Mình vẫn ổn, công việc dạo này thế nào?'],
            ['sender' => 'Nguyễn Văn A', 'text' => 'Khá bận, nhưng vẫn ổn.'],
        ];

        return $this->render('dashboard.html.twig', [
            'user' => $user,
            'chat_list' => $chatList,
            'current_chat' => $currentChat,
            'messages' => $messages,
        ]);
    }
}
