<?php

namespace App\Controller;

use App\Service\FriendService;
use App\Service\User\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    private UserServiceInterface $userService;

    private FriendService $friendService;

    public function __construct(UserServiceInterface $userService, FriendService $friendService){
        $this->userService = $userService;
        $this->friendService = $friendService;
    }

    #[Route('/dashboard/news', name: 'news')]
    public function storiesAction(): Response
    {

        $friendList = $this->friendService->getAcceptedFriends();

        // Thêm 5 dữ liệu test vào danh sách bạn bè
        $friendList = array_merge($friendList, [
            [
                "id" => 11,
                "name" => "Nguyễn Văn A",
                "avatar" => "https://randomuser.me/api/portraits/men/1.jpg",
                "slug" => "nguyenvana"
            ],
            [
                "id" => 12,
                "name" => "Trần Thị B",
                "avatar" => "https://randomuser.me/api/portraits/women/2.jpg",
                "slug" => "tranthib"
            ],
            [
                "id" => 13,
                "name" => "Lê Hồng C",
                "avatar" => "https://randomuser.me/api/portraits/men/3.jpg",
                "slug" => "lehongc"
            ],
            [
                "id" => 14,
                "name" => "Phan Thanh D",
                "avatar" => "https://randomuser.me/api/portraits/women/4.jpg",
                "slug" => "phanthanhd"
            ],
            [
                "id" => 15,
                "name" => "Hoàng Minh E",
                "avatar" => "https://randomuser.me/api/portraits/men/5.jpg",
                "slug" => "hoangminhe"
            ],
            [
                "id" => 14,
                "name" => "Phan Thanh D",
                "avatar" => "https://randomuser.me/api/portraits/women/4.jpg",
                "slug" => "phanthanhd"
            ],
            [
                "id" => 15,
                "name" => "Hoàng Minh E",
                "avatar" => "https://randomuser.me/api/portraits/men/5.jpg",
                "slug" => "hoangminhe"
            ],
        ]);

        $profile = $this->userService->getCurrentUserProfile();

        $user = [
            'avatar' => '/images/user_avatar.jpg'
        ];

        $stories = [
            [
                'name' => 'Thy Nguyen Nguyen',
                'avatar' => '/images/story1_avatar.jpg',
                'image' => '/images/story1.jpg'
            ],
            [
                'name' => 'Hạ Băng',
                'avatar' => '/images/story2_avatar.jpg',
                'image' => '/images/story2.jpg'
            ],
            [
                'name' => 'Lê Phương Khánh Như',
                'avatar' => '/images/story3_avatar.jpg',
                'image' => '/images/story3.jpg'
            ],
            [
                'name' => 'Gia Vinh',
                'avatar' => '/images/story4_avatar.jpg',
                'image' => '/images/story4.jpg'
            ]
        ];

        $post = [
            'page_name' => 'GOAL Vietnam',
            'page_avatar' => '',
            'time' => '32 phút trước',
            'content' => [
                '❌ Duran, Ronaldo tịt ngòi',
                '❌ Al-Nassr thua ngược 2-3 trên sân nhà',
                'Và đây là điểm số của Top 4 Saudi Pro League hiện tại 😬'
            ],
            'image' => '',
            'league_table' => [
                ['rank' => 1, 'name' => 'AL-ITTIHAD', 'matches' => 20, 'goal_difference' => 31, 'points' => 52],
                ['rank' => 2, 'name' => 'AL-HILAL', 'matches' => 20, 'goal_difference' => 40, 'points' => 48],
                ['rank' => 3, 'name' => 'AL-QADSIAH', 'matches' => 21, 'goal_difference' => 19, 'points' => 47],
                ['rank' => 4, 'name' => 'AL-NASSR', 'matches' => 21, 'goal_difference' => 23, 'points' => 44],
            ],
            'likes' => 200,
            'comments' => 50,
            'shares' => 10
        ];

        return $this->render('news.html.twig', [
            'user' => $user,
            'stories' => $stories,
            'post' => $post,
            'profile' => $profile,
            'friendList' => $friendList
        ]);
    }
}