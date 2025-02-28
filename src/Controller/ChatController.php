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

        // Lấy danh sách bạn bè và tin nhắn cuối cùng
        $friends = $this->messageService->getFriendsWithLastMessage($user);

        // Lấy thông tin người nhận
        $receiver = $this->userService->getUserProfileById($id);
        if (!$receiver) {
            throw $this->createNotFoundException("Người nhận không tồn tại.");
        }

        // Lấy danh sách tin nhắn giữa user hiện tại và người nhận
        $messages = $this->messageService->getChatHistory($currentUser->getId(), $id);

        // Cấu trúc dữ liệu của `currentChat` để truyền vào giao diện
        $currentChat = [
            'id' => $receiver['id'],
            'name' => $receiver['name'],
            'avatar' => $receiver['avatar'],
            'messages' => []
        ];

        // Xử lý danh sách tin nhắn
        foreach ($messages as $message) {
            $currentChat['messages'][] = [
                'senderId' => $message['senderId'],
                'text' => $message['content'],
                'time' => (new \DateTime($message['sentAt']))->format('H:i'),
            ];
        }

        // Truyền dữ liệu user chính xác
        $userData = [
            'id' => $currentUser->getId(),
            'name' => 'Bạn',
            'avatar' => $profile['avatar'],
        ];

        return $this->render('message.html.twig', [
            'chat_list' => $friends, // Danh sách cuộc trò chuyện
            'current_chat' => $currentChat, // Cuộc trò chuyện hiện tại
            'user' => $userData,
            'profile' => $profile,
            'receiver' => $receiver,
            'friends' => $friends,
            'messages' => $messages, // ✅ Sửa lỗi key '$messages'
        ]);
    }

}
