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

//       dump($otherPeople); exit();

        $friendRequests = [
            [
                'name' => 'Như Như',
                'avatar' => '/images/friend1.jpg',
                'mutual_friends' => null,
                'mutual_avatar' => null,
            ],
            [
                'name' => 'Huỳnh Ngọc Huyền Anh',
                'avatar' => '/images/friend2.jpg',
                'mutual_friends' => null,
                'mutual_avatar' => null,
            ],
            [
                'name' => 'Nguyễn An',
                'avatar' => '/images/friend3.jpg',
                'mutual_friends' => null,
                'mutual_avatar' => null,
            ],
            [
                'name' => 'Mai Phi',
                'avatar' => '/images/friend4.jpg',
                'mutual_friends' => '4',
                'mutual_avatar' => '/images/mutual_friend.jpg',
            ],
            [
                'name' => 'Mai Phi',
                'avatar' => '/images/friend4.jpg',
                'mutual_friends' => '4',
                'mutual_avatar' => '/images/mutual_friend.jpg',
            ]
        ];

        $users = [
            [
                'name' => 'Như Như',
                'avatar' => '/images/user1.jpg',
                'mutual_friends' => null,
                'mutual_avatar' => null,
            ],
            [
                'name' => 'Huỳnh Ngọc Huyền Anh',
                'avatar' => '/images/user2.jpg',
                'mutual_friends' => null,
                'mutual_avatar' => null,
            ],
            [
                'name' => 'Nguyễn An',
                'avatar' => '/images/user3.jpg',
                'mutual_friends' => '3',
                'mutual_avatar' => '/images/mutual_friend.jpg',
            ],
            [
                'name' => 'Mai Phi',
                'avatar' => '/images/user4.jpg',
                'mutual_friends' => '5',
                'mutual_avatar' => '/images/mutual_friend2.jpg',
            ]
        ];

        return $this->render('friend.html.twig', [
            'friend_requests' => $friendRequests,
            'users' => $users,
            'profile' => $profile,
            'otherPeople' => $otherPeople
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
}