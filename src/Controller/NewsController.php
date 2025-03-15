<?php

namespace App\Controller;

use App\Service\FriendService;
use App\Service\PostService;
use App\Service\User\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    private UserServiceInterface $userService;

    private FriendService $friendService;
    private PostService $postService;


    public function __construct(UserServiceInterface $userService, FriendService $friendService,
    PostService $postService){
        $this->userService = $userService;
        $this->friendService = $friendService;
        $this->postService = $postService;
    }

    #[Route('/dashboard/news', name: 'news')]
    public function storiesAction(): Response
    {

        $friendList = $this->friendService->getAcceptedFriends();

        $profile = $this->userService->getCurrentUserProfile();

        // ✅ Lấy danh sách bài viết từ PostService
        $currentUser = $this->userService->getCurrentUser();
        $currentUserId = $currentUser ? $currentUser->getId() : null;

        $posts = $this->postService->getRecentPosts(50, $currentUserId);

        return $this->render('news.html.twig', [
            'profile' => $profile,
            'friendList' => $friendList,
            'posts' => $posts,
        ]);
    }
}