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
        $user = $this->getUser(); // Người dùng hiện tại

        if (!$user) {
            throw $this->createAccessDeniedException('Bạn chưa đăng nhập.');
        }

        $friends = $this->messageService->getFriendsWithLastMessage($user);

        return $this->render('dashboard.html.twig', [
            'user' => $user,
            'profile' => $profile,
            'friends' => $friends,
        ]);
    }
}
