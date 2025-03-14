<?php

namespace App\Service;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;

class PostService
{
    private PostRepository $postRepository;
    private EntityManagerInterface $entityManager;

    private UserService $userService;

    public function __construct(PostRepository $postRepository, EntityManagerInterface $entityManager,
    UserService $userService)
    {
        $this->postRepository = $postRepository;
        $this->entityManager = $entityManager;
        $this->userService = $userService;
    }

    public function createPost(string $idAuthor, string $content): Post
    {
        $post = new Post();
        $post->setIdAuthor($idAuthor);
        $post->setContent($content);

        // ✅ Lưu bài viết vào database
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $post;
    }

    public function getRecentPosts(int $limit = 50): array
    {
        $posts = $this->postRepository->findBy([], ['createdAt' => 'DESC'], $limit);

        foreach ($posts as $post) {
            $authorId = $post->getIdAuthor();
            $author = $this->userService->getUserProfileById($authorId);
            $post->avatarAuthor = $author['avatar'];
            $post->authorName = $author['name'];

            // Cộng thêm 7 tiếng vào createdAt
            if ($post->getCreatedAt() instanceof \DateTime) {
                $adjustedDate = (clone $post->getCreatedAt())->modify('+7 hours');
                $post->setCreatedAt($adjustedDate); // Dùng setter thay vì gán trực tiếp
            }
        }

        return $posts;
    }
}
