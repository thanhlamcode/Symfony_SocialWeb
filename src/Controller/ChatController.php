<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    #[Route('/dashboard/message/{id}', name: 'chat_message')]
    public function message(int $id): Response
    {
        // Danh sách cuộc trò chuyện
        $chatList = [
            [
                'id' => 1,
                'name' => 'Nguyễn Văn A',
                'avatar' => '/images/avatar1.png',
                'last_message' => 'Bạn khỏe không?',
                'time' => '10:30 AM'
            ],
            [
                'id' => 2,
                'name' => 'Trần Thị B',
                'avatar' => '/images/avatar2.png',
                'last_message' => 'Mai gặp nhau nhé!',
                'time' => '09:15 AM'
            ],
            [
                'id' => 3,
                'name' => 'Lê Minh C',
                'avatar' => '/images/avatar3.png',
                'last_message' => 'Haha, đúng rồi đó!',
                'time' => 'Hôm qua'
            ]
        ];

        // Tìm cuộc trò chuyện hiện tại dựa trên ID
        $currentChat = null;
        foreach ($chatList as $chat) {
            if ($chat['id'] === $id) {
                $currentChat = $chat;
                break;
            }
        }

        // Nếu không tìm thấy, trả về lỗi 404
        if ($currentChat === null) {
            throw $this->createNotFoundException("Cuộc trò chuyện không tồn tại.");
        }

        // Giả lập user đăng nhập
        $user = [
            'name' => 'Bạn',
            'avatar' => '/images/user.png'
        ];

        return $this->render('message.html.twig', [
            'chat_list' => $chatList,  // Toàn bộ danh sách trò chuyện
            'current_chat' => $currentChat, // Cuộc trò chuyện đang hiển thị
            'user' => $user
        ]);
    }
}
