<?php

namespace App\Controller;

use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService){
        $this->userService = $userService;
    }

    #[Route('/dashboard/message/{id}', name: 'chat_message')]
    public function message(int $id): Response
    {
        $profile = $this->userService->getCurrentUserProfile();

        // Danh sách cuộc trò chuyện với tin nhắn
        $chatList = [
            [
                'id' => 1,
                'name' => 'Nguyễn Văn A',
                'avatar' => '/images/avatar1.png',
                'last_message' => 'Bạn khỏe không?',
                'time' => '10:30 AM',
                'messages' => [
                    ['sender' => 'Nguyễn Văn A', 'text' => 'Chào bạn!', 'time' => '10:25 AM'],
                    ['sender' => 'Bạn', 'text' => 'Chào, bạn khỏe không?', 'time' => '10:27 AM'],
                ]
            ],
            [
                'id' => 2,
                'name' => 'Trần Thị B',
                'avatar' => '/images/avatar2.png',
                'last_message' => 'Mai gặp nhau nhé!',
                'time' => '09:15 AM',
                'messages' => [
                    ['sender' => 'Trần Thị B', 'text' => 'Mai có rảnh không?', 'time' => '09:10 AM'],
                    ['sender' => 'Bạn', 'text' => 'Ừ, mai gặp nhé!', 'time' => '09:12 AM'],
                ]
            ],
            [
                'id' => 3,
                'name' => 'Lê Minh C',
                'avatar' => '/images/avatar3.png',
                'last_message' => 'Haha, đúng rồi đó!',
                'time' => 'Hôm qua',
                'messages' => [
                    ['sender' => 'Bạn', 'text' => 'Hôm qua vui quá!', 'time' => 'Hôm qua'],
                    ['sender' => 'Lê Minh C', 'text' => 'Haha, đúng rồi đó!', 'time' => 'Hôm qua'],
                ]
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
            'user' => $user,
            'profile' => $profile
        ]);
    }
}
