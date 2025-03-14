<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function save(Post $post, bool $flush = true): void
    {
        $this->getEntityManager()->persist($post);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function updatePost(Post $post): void
    {
        $em = $this->getEntityManager();
        $em->persist($post);
        $em->flush();
    }

    public function deletePost(Post $post): void
    {
        $em = $this->getEntityManager();
        $em->remove($post);
        $em->flush();
    }
}
