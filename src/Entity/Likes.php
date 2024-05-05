<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


use App\Repository\likesRepository;


#[ORM\Entity(repositoryClass: LikesRepository::class)]

class Likes
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "like_id", type: "integer", nullable: false)]
    private ?int $likeId;

    #[ORM\Column(name: "post_id", type: "integer", nullable: true)]
    private ?int $postId;

    #[ORM\Column(name: "user_id", type: "integer", nullable: true)]
    private ?int $userId;

    #[ORM\Column(name: "reaction_type", type: "string", columnDefinition:"ENUM('NON', 'LIKE', 'LOVE', 'CARE', 'HAHA', 'WOW','SAD','ANGRY')", nullable: true, options: ["default" => "NON"])]
    private ?string $reactionType = 'NON';

    #[ORM\Column(name: "created_at", type: "datetime", nullable: false)]
    private \DateTimeInterface $createdAt;

    public function getLikeId(): ?int
    {
        return $this->likeId;
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function setPostId(?int $postId): static
    {
        $this->postId = $postId;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getReactionType(): ?string
    {
        return $this->reactionType;
    }

    public function setReactionType(?string $reactionType): static
    {
        $this->reactionType = $reactionType;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}