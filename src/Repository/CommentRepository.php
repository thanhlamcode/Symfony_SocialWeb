<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Lấy danh sách comment của một bài viết
     */
    public function findByPostId(int $postId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.postId = :postId')
            ->setParameter('postId', $postId)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Lưu hoặc cập nhật comment
     */
    public function save(Comment $comment, bool $flush = true): void
    {
        $this->getEntityManager()->persist($comment);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Xóa comment
     */
    public function deleteComment(Comment $comment): void
    {
        $em = $this->getEntityManager();
        $em->remove($comment);
        $em->flush();
    }
}
