<?php

namespace App\Controller;

use App\Service\CommentService;
use App\Service\User\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    private CommentService $commentService;
    private UserServiceInterface $userService; // 🆕 Inject UserServiceInterface

    public function __construct(CommentService $commentService, UserServiceInterface $userService)
    {
        $this->commentService = $commentService;
        $this->userService = $userService; // 🆕 Inject UserServiceInterface
    }

    /**
     * API: Lấy danh sách comment của một bài viết
     */
    #[Route('/post/comments/{postId}', methods: ['GET'])]
    public function getComments(int $postId): JsonResponse
    {
        $comments = $this->commentService->getCommentsByPost($postId);
        return $this->json(['comments' => $comments]);
    }

    /**
     * API: Thêm comment vào bài viết
     */
    #[Route('/post/comment/{postId}', methods: ['POST'])]
    public function addComment(Request $request, int $postId): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['content']) || empty(trim($data['content']))) {
                return $this->json(['error' => 'Nội dung bình luận không hợp lệ'], 400);
            }

            $user = $this->userService->getCurrentUser(); // 🆕 Dùng UserService
            if (!$user) {
                return $this->json(['error' => 'Bạn cần đăng nhập để bình luận.'], 401);
            }

            $comment = $this->commentService->createComment($postId, $user->getId(), $data['content']);

            return $this->json([
                'success' => true,
                'id' => $comment->getId(),
                'authorName' => $user->getEmail(), // 🆕 Lấy email nếu không có username
                'authorAvatar' => $this->userService->getCurrentUserProfile()['avatar'] ?? null,
                'content' => $comment->getContent()
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Sửa comment
     */
    #[Route('/post/comment/edit/{commentId}', methods: ['PUT'])]
    public function editComment(Request $request, int $commentId): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->userService->getCurrentUser(); // 🆕 Dùng UserService

        if (!$user) {
            return $this->json(['error' => 'Bạn cần đăng nhập để chỉnh sửa.'], 401);
        }

        try {
            $comment = $this->commentService->updateComment($commentId, $data['content'], $user->getId());
            return $this->json(['success' => true, 'content' => $comment->getContent()]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * API: Xóa comment
     */
    #[Route('/post/comment/delete/{commentId}', methods: ['DELETE'])]
    public function deleteComment(int $commentId): JsonResponse
    {
        $user = $this->userService->getCurrentUser(); // 🆕 Dùng UserService

        if (!$user) {
            return $this->json(['error' => 'Bạn cần đăng nhập để xóa bình luận.'], 401);
        }

        try {
            $this->commentService->deleteComment($commentId, $user->getId());
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
