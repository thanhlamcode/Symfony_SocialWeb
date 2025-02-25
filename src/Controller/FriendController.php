<?php

namespace App\Controller;

use App\Service\FriendService;
use App\Service\User\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        $data = $this->friendService->getAvailableUsers();

        dump($data); exit();

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
            'profile' => $profile
        ]);
    }
}