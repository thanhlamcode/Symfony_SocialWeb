<?php

namespace App\Controller;

use App\Service\FriendService;
use App\Service\MessageService;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    private UserService $userService;
    private MessageService $messageService;

    private FriendService $friendService;

    public function __construct(UserService $userService, MessageService $messageService, FriendService $friendService){
        $this->userService = $userService;
        $this->messageService = $messageService;
        $this->friendService = $friendService;
    }

    #[Route('/dashboard/message/{id}', name: 'chat_message')]
    public function message(int $id): Response
    {
        $profile = $this->userService->getCurrentUserProfile();

        // Lấy thông tin user hiện tại
        $currentUser = $this->userService->getCurrentUser();
        if (!$currentUser) {
            throw $this->createNotFoundException("Bạn cần đăng nhập để xem tin nhắn.");
        }

        $user = $this->getUser(); // Người dùng hiện tại

        if (!$user) {
            throw $this->createAccessDeniedException('Bạn chưa đăng nhập.');
        }

        $friends = $this->messageService->getFriendsWithLastMessage($user);

        // Lấy thông tin người nhận
        $receiver = $this->userService->getUserProfileById($id);
        if (!$receiver) {
            throw $this->createNotFoundException("Người nhận không tồn tại.");
        }

        // Lấy danh sách tin nhắn giữa user hiện tại và người nhận
        $messages = $this->messageService->getChatHistory($currentUser->getId(), $id);


        // Data mẫu
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
                'id' => 4,
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
            ],
            [
                'id' => 7,
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

        // User đăng nhập
        $user = [
            'name' => 'Bạn',
            'avatar' => $profile['avatar'],
        ];
//
//        dump($user);
//        dump($receiver);
//        dump($messages); exit();


        return $this->render('message.html.twig', [
            'chat_list' => $chatList,  // Toàn bộ danh sách trò chuyện
            'current_chat' => $currentChat, // Cuộc trò chuyện đang hiển thị
            'user' => $user,
            'profile' => $profile,
            'receiver' => $receiver,
            'friends' => $friends,
            '$messages' => $messages
        ]);
    }
}
