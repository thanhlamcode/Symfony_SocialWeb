<?php

namespace App\Controller;

use App\Service\FriendService;
use App\Service\MessageService;
use App\Service\User\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends AbstractController
{
    private UserServiceInterface $userService;
    private FriendService $friendService;

    private MessageService $messageService;

    public function __construct(UserServiceInterface $userService, FriendService $friendService, MessageService $messageService){
        $this->userService = $userService;
        $this->friendService = $friendService;
        $this->messageService = $messageService;
    }

    public function dashboard(): Response
    {

        $profile = $this->userService->getCurrentUserProfile();
        $friends = $this->friendService->getAcceptedFriends();
        $user = $this->getUser(); // Người dùng hiện tại

        if (!$user) {
            throw $this->createAccessDeniedException('Bạn chưa đăng nhập.');
        }

        // Duyệt danh sách bạn bè và bổ sung last_message
        foreach ($friends as &$friend) {
            $lastMessage = $this->messageService->getLastMessage($user->getId(), $friend['id']);
            $friend['last_message'] = $lastMessage ? $lastMessage['content'] : 'Chưa có tin nhắn';
            $friend['last_message_time'] = $lastMessage ? $lastMessage['sentAt'] : null;
        }

        dump($user);
        // Kiểm tra kết quả
        dump($friends); exit();


        // Danh sách chat (dữ liệu giả lập)
        $chatList = [
            [
                'id' => 1, // Thêm id
                'name' => 'Nguyễn Văn A',
                'avatar' => '/images/avatar1.png',
                'last_message' => 'Bạn khỏe không?',
                'time' => '10:30 AM'
            ],
            [
                'id' => 2, // Thêm id
                'name' => 'Trần Thị B',
                'avatar' => '/images/avatar2.png',
                'last_message' => 'Mai gặp nhau nhé!',
                'time' => '09:15 AM'
            ],
            [
                'id' => 3, // Thêm id
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
            'profile' => $profile
        ]);
    }
}
