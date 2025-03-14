<?php

namespace App\Controller;

use App\Service\PostService;
use App\Service\User\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    private PostService $postService;
    private UserServiceInterface $userService;

    public function __construct(PostService $postService, UserServiceInterface $userService)
    {
        $this->postService = $postService;
        $this->userService = $userService;
    }

    #[Route('/post', name: 'create_post', methods: ['POST'])]
    public function createPost(Request $request): Response
    {
        // ✅ Lấy user hiện tại từ UserService
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            $this->addFlash('error', 'Bạn chưa đăng nhập!');
            return $this->redirectToRoute('home');
        }

        // ✅ Lấy nội dung từ form
        $content = $request->request->get('content', '');

        if (empty(trim($content))) {
            $this->addFlash('error', 'Nội dung không được để trống.');
            return $this->redirectToRoute('dashboard');
        }

        // ✅ Đăng bài thông qua PostService
        $this->postService->createPost($user->getId(), $content);

        // ✅ Chuyển hướng về trang dashboard với thông báo thành công
        $this->addFlash('success', 'Bài viết đã được đăng!');
        return $this->redirectToRoute('dashboard');
    }
}
