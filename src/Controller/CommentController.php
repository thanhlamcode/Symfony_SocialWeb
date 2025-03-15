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
    private UserServiceInterface $userService;

    public function __construct(CommentService $commentService, UserServiceInterface $userService)
    {
        $this->commentService = $commentService;
        $this->userService = $userService;
    }

    /**
     * API: Lấy danh sách comment của một bài viết
     */
    #[Route('/post/comments/{postId}', methods: ['GET'])]
    public function getComments(int $postId): JsonResponse
    {
        $comments = $this->commentService->getRecentComments($postId);

        return $this->json([
            'success' => true,
            'comments' => array_map(function ($comment) {
                return [
                    'id' => $comment->getId(),
                    'authorId' => $comment->getAuthorId(),
                    'authorName' => $comment->authorName ?? 'Ẩn danh',
                    'authorAvatar' => $comment->authorAvatar ?? null,
                    'content' => $comment->getContent(),
                    'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
                    'likes' => $comment->getLikes(),
                    'likedBy' => $comment->getLikedBy()
                ];
            }, $comments)
        ]);
    }

    /**
     * API: Thêm comment vào bài viết
     */
    #[Route('/post/comment/{postId}', methods: ['POST'])]
    public function addComment(Request $request, int $postId): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->userService->getCurrentUser();

            if (!$user) {
                return $this->json(['error' => 'Bạn cần đăng nhập để bình luận.'], 401);
            }

            if (!isset($data['content']) || empty(trim($data['content']))) {
                return $this->json(['error' => 'Nội dung bình luận không hợp lệ.'], 400);
            }

            $comment = $this->commentService->createComment($postId, $user->getId(), $data['content']);

            $profile = $this->userService->getUserProfileById($user->getId());

            return $this->json([
                'success' => true,
                'id' => $comment->getId(),
                'authorName' => $profile['name'],
                'authorAvatar' => $this->userService->getCurrentUserProfile()['avatar'] ?? null,
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s')
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
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->userService->getCurrentUser();

            if (!$user) {
                return $this->json(['error' => 'Bạn cần đăng nhập để chỉnh sửa.'], 401);
            }

            if (!isset($data['content']) || empty(trim($data['content']))) {
                return $this->json(['error' => 'Nội dung bình luận không hợp lệ.'], 400);
            }

            $comment = $this->commentService->updateComment($commentId, $data['content'], $user->getId());

            return $this->json([
                'success' => true,
                'content' => $comment->getContent(),
                'updatedAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s')
            ]);
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
        try {
            $user = $this->userService->getCurrentUser();

            if (!$user) {
                return $this->json(['error' => 'Bạn cần đăng nhập để xóa bình luận.'], 401);
            }

            $this->commentService->deleteComment($commentId, $user->getId());

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * API: Like hoặc Unlike một comment
     */
    #[Route('/post/comment/like/{commentId}', methods: ['POST'])]
    public function likeComment(int $commentId): JsonResponse
    {
        try {
            $user = $this->userService->getCurrentUser();

            if (!$user) {
                return $this->json(['error' => 'Bạn cần đăng nhập để like bình luận.'], 401);
            }

            $comment = $this->commentService->likeComment($commentId, $user->getId());

            return $this->json([
                'success' => true,
                'likes' => $comment->getLikes(),
                'likedBy' => $comment->getLikedBy()
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
