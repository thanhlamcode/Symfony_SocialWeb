<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PostRepository;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $idAuthor;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'integer')]
    private int $likes = 0;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'json')]
    private array $likedBy = [];

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
        }
        return $this;
    }

    public function removeLike(int $userId): self
    {
        $this->likedBy = array_filter($this->likedBy, fn($id) => $id !== $userId);
        return $this;
    }

    public function getLikeCount(): int
    {
        return count($this->likedBy);
    }

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdAuthor(): string
    {
        return $this->idAuthor;
    }

    public function setIdAuthor(string $idAuthor): self
    {
        $this->idAuthor = $idAuthor;
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

    public function getLikes(): int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): self
    {
        $this->likes = $likes;
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

}
