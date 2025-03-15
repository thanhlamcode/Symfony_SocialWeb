<?php

namespace App\Service;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CommentService
{
    private CommentRepository $commentRepository;

    public function __construct(CommentRepository $commentRepository, EntityManagerInterface $entityManager)
    {
        $this->commentRepository = $commentRepository;
    }

    /**
     * Lấy tất cả comment của một bài viết
     */
    public function getCommentsByPost(int $postId): array
    {
        return $this->commentRepository->findByPostId($postId);
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

        $this->commentRepository->save($comment); // Dùng cách lưu mới
        return $comment;
    }

    /**
     * Chỉnh sửa comment (chỉ tác giả mới có quyền sửa)
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
        $this->commentRepository->save($comment); // Dùng cách lưu mới
        return $comment;
    }

    /**
     * Xóa comment (chỉ tác giả hoặc admin mới có quyền)
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

        $this->commentRepository->deleteComment($comment); // Dùng cách xóa mới
    }
}
