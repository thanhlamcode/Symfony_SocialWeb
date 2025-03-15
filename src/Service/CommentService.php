<?php

namespace App\Service;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CommentService
{
    private CommentRepository $commentRepository;
    private EntityManagerInterface $entityManager;
    private UserService $userService;

    public function __construct(CommentRepository $commentRepository, EntityManagerInterface $entityManager, UserService $userService)
    {
        $this->commentRepository = $commentRepository;
        $this->entityManager = $entityManager;
        $this->userService = $userService;
    }

    /**
     * Lấy tất cả comment của một bài viết theo thứ tự mới nhất.
     */
    public function getRecentComments(int $postId, int $limit = 50): array
    {
        $comments = $this->commentRepository->findByPostId($postId);

        foreach ($comments as $comment) {
            $author = $this->userService->getUserProfileById($comment->getAuthorId());
            $comment->authorAvatar = $author['avatar'] ?? null;
            $comment->authorName = $author['name'] ?? 'Ẩn danh';
        }

        return $comments;
    }

    /**
     * Tạo mới comment
     */
    public function createComment(int $postId, int $authorId, string $content): Comment
    {
        $comment = new Comment();
        $comment->setPostId($postId);
        $comment->setAuthorId($authorId);
        $comment->setContent($content);
        $comment->setCreatedAt(new \DateTime());

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return $comment;
    }

    /**
     * Cập nhật bình luận (chỉ tác giả mới có quyền sửa)
     */
    public function updateComment(int $commentId, string $newContent, int $currentUserId): Comment
    {
        $comment = $this->commentRepository->find($commentId);

        if (!$comment) {
            throw new \Exception('Bình luận không tồn tại.');
        }

        if ($comment->getAuthorId() !== $currentUserId) {
            throw new AccessDeniedException('Bạn không có quyền sửa bình luận này.');
        }

        $comment->setContent($newContent);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return $comment;
    }

    /**
     * Xóa bình luận (chỉ tác giả hoặc admin mới có quyền)
     */
    public function deleteComment(int $commentId, int $currentUserId): void
    {
        $comment = $this->commentRepository->find($commentId);

        if (!$comment) {
            throw new \Exception('Bình luận không tồn tại.');
        }

        if ($comment->getAuthorId() !== $currentUserId) {
            throw new AccessDeniedException('Bạn không có quyền xóa bình luận này.');
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();
    }

    /**
     * Like/Unlike bình luận
     */
    public function likeComment(int $commentId, int $userId): Comment
    {
        $comment = $this->commentRepository->find($commentId);

        if (!$comment) {
            throw new \Exception('Bình luận không tồn tại.');
        }

        if (!in_array($userId, $comment->getLikedBy())) {
            $comment->addLike($userId);
        } else {
            $comment->removeLike($userId);
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return $comment;
    }

    /**
     * Lưu comment vào database
     */
    public function save(Comment $comment): void
    {
        $this->entityManager->persist($comment);
        $this->entityManager->flush();
    }
}
