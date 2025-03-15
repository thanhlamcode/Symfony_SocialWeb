<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommentRepository;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $postId;

    #[ORM\Column(type: 'integer')]
    private int $authorId;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'integer')]
    private int $likes = 0;

    #[ORM\Column(type: 'json')]
    private array $likedBy = [];

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    // Getters & Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): self
    {
        $this->postId = $postId;
        return $this;
    }

    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    public function setAuthorId(int $authorId): self
    {
        $this->authorId = $authorId;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getLikes(): int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): self
    {
        $this->likes = $likes;
        return $this;
    }

    public function getLikedBy(): array
    {
        return $this->likedBy;
    }

    public function setLikedBy(array $likedBy): self
    {
        $this->likedBy = $likedBy;
        return $this;
    }

    public function addLike(int $userId): self
    {
        if (!in_array($userId, $this->likedBy)) {
            $this->likedBy[] = $userId;
            $this->likes = count($this->likedBy);
        }
        return $this;
    }

    public function removeLike(int $userId): self
    {
        $this->likedBy = array_filter($this->likedBy, fn($id) => $id !== $userId);
        $this->likes = count($this->likedBy);
        return $this;
    }
}
