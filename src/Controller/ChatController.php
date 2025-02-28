<?php

namespace App\Controller;

use App\Service\FriendService;
use App\Service\MessageService;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    private MessageService $messageService;

    public function __construct(MessageService $messageService){
        $this->messageService = $messageService;
    }

    #[Route('/dashboard/message/{id}', name: 'chat_message')]
    public function message(int $id): Response
    {
        // Lấy user hiện tại
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw $this->createAccessDeniedException("Bạn chưa đăng nhập.");
        }

        // Gọi Service để lấy dữ liệu chat
        $chatData = $this->messageService->getChatData($id, $currentUser);

        return $this->render('message.html.twig', $chatData);
    }

    #[Route('/dashboard/message/send/{id}', name: 'send_message', methods: ['POST'])]
    public function sendMessage(int $id, Request $request): RedirectResponse
    {
        // Lấy user hiện tại
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw $this->createAccessDeniedException("Bạn chưa đăng nhập.");
        }

        // Lấy nội dung tin nhắn từ request
        $content = trim($request->request->get('message'));
        if (empty($content)) {
            return $this->redirectToRoute('chat_message', ['id' => $id]);
        }

        // Gửi tin nhắn qua MessageService
        $this->messageService->sendMessage($currentUser->getId(), $id, $content);

        // Chuyển hướng về trang chat sau khi gửi tin nhắn
        return $this->redirectToRoute('chat_message', ['id' => $id]);
    }

}
