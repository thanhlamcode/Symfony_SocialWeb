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

}
