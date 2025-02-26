<?php

namespace App\Controller;

use App\Service\FriendService;
use App\Service\User\UserServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FriendController extends AbstractController
{
    private UserServiceInterface $userService;
    private FriendService $friendService;

    public function __construct(UserServiceInterface $userService, FriendService $friendService){
        $this->userService = $userService;
        $this->friendService = $friendService;
    }

    #[Route('/dashboard/friend', name: 'friends')]
    public function friends(): Response
    {
        $profile = $this->userService->getCurrentUserProfile();

        $otherPeople = $this->friendService->getAvailableUsers();

        $friendRequests = $this->friendService->getFriendRequests();

        return $this->render('friend.html.twig', [
            'friend_requests' => $friendRequests,
            'profile' => $profile,
            'otherPeople' => $otherPeople,
            'friendRequests' => $friendRequests,
        ]);
    }

    #[Route('/friend-request/toggle', name: 'toggle_friend_request', methods: ['POST'])]
    public function toggleFriendRequest(Request $request, FriendService $friendService): Response
    {
        $receiverId = $request->request->get('receiverId');
        $csrfToken = $request->request->get('_token');

        // Kiểm tra CSRF token
        if (!$this->isCsrfTokenValid('toggle_friend_request', $csrfToken)) {
            $this->addFlash('error', 'CSRF Token không hợp lệ!');
            return $this->redirectToRoute('friends'); // Hoặc route phù hợp
        }

        // Kiểm tra trạng thái hiện tại
        $isSent = $friendService->isFriendRequestSent($receiverId);

        if ($isSent) {
            $friendService->cancelFriendRequest($receiverId);
            $this->addFlash('success', 'Hủy lời mời kết bạn thành công!');
        } else {
            $friendService->sendFriendRequest($receiverId);
            $this->addFlash('success', 'Gửi lời mời kết bạn thành công!');
        }

        return $this->redirectToRoute('friends'); // Hoặc route phù hợp
    }

    #[Route('/friend-request/accept', name: 'accept_friend_request', methods: ['POST'])]
    public function acceptFriendRequest(Request $request, FriendService $friendService): Response
    {
        $senderId = $request->request->get('senderId');
        $csrfToken = $request->request->get('_token');

        // Kiểm tra CSRF token
        if (!$this->isCsrfTokenValid('accept_friend_request', $csrfToken)) {
            $this->addFlash('error', 'CSRF Token không hợp lệ!');
            return $this->redirectToRoute('friends');
        }

        // Chấp nhận lời mời kết bạn
        $friendService->acceptFriendRequest($senderId);
        $this->addFlash('success', 'Bạn đã chấp nhận lời mời kết bạn.');

        return $this->redirectToRoute('friends');
    }

    #[Route('/friend-request/decline', name: 'decline_friend_request', methods: ['POST'])]
    public function declineFriendRequest(Request $request, FriendService $friendService): Response
    {
        $senderId = $request->request->get('senderId');
        $csrfToken = $request->request->get('_token');

        // Kiểm tra CSRF token
        if (!$this->isCsrfTokenValid('decline_friend_request', $csrfToken)) {
            $this->addFlash('error', 'CSRF Token không hợp lệ!');
            return $this->redirectToRoute('friends');
        }

        // Hủy lời mời kết bạn
        $friendService->declineFriendRequest($senderId);
        $this->addFlash('success', 'Bạn đã từ chối lời mời kết bạn.');

        return $this->redirectToRoute('friends');
    }

}