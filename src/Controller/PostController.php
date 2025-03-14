<?php

namespace App\Controller;

use App\Service\PostService;
use App\Service\User\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        return $this->redirectToRoute('news');
    }

    /**
     * @Route("/post/edit/{id}", name="post_edit", methods={"POST"})
     */
    #[Route('/post/edit/{id}', name: 'post_edit', methods: ['POST'])]
    public function editPost(int $id, Request $request): JsonResponse
    {
        // ✅ Lấy user hiện tại từ UserService
        $currentUser = $this->userService->getCurrentUser();
        if (!$currentUser) {
            return $this->json(['message' => 'Bạn cần đăng nhập'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $newContent = $data['content'] ?? '';

        try {
            $post = $this->postService->updatePost($id, $newContent, $currentUser->getId());
            return $this->json([
                'message' => 'Bài viết đã được cập nhật.',
                'post' => [
                    'id' => $post->getId(),
                    'content' => $post->getContent(),
                    'updatedAt' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/post/delete/{id}", name="post_delete", methods={"DELETE"})
     */
    #[Route('/post/delete/{id}', name: 'post_delete', methods: ['DELETE'])]
    public function deletePost(int $id): JsonResponse
    {
        // ✅ Lấy user hiện tại từ UserService
        $currentUser = $this->userService->getCurrentUser();
        if (!$currentUser) {
            return $this->json(['message' => 'Bạn cần đăng nhập'], 401);
        }

        try {
            $this->postService->deletePost($id, $currentUser->getId());
            return $this->json(['message' => 'Bài viết đã bị xóa.']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/post/{id}", name="get_post", methods={"GET"})
     */
    #[Route('/post/{id}', name: 'get_post', methods: ['GET'])]
    public function getPost(int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id);

        if (!$post) {
            return $this->json(['message' => 'Bài viết không tồn tại'], 404);
        }

        return $this->json([
            'id' => $post->getId(),
            'content' => $post->getContent()
        ]);
    }


}
