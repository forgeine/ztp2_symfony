<?php

/**
 * Comment.
 */

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Comment.
 */
#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Table(name: 'comments')]
class Comment
{
    /**
     * Primary key id.
     *
     * @var int|null Id
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * createdAt.
     *
     * @var \DateTimeImmutable|null Created at
     */
    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * content.
     *
     * @var string|null Content
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    private ?string $content = null;

    /**
     * Newspaper relation.
     *
     * @var Newspaper|null Newspaper
     */
    #[ORM\ManyToOne(targetEntity: Newspaper::class, inversedBy: 'comments')]
    private ?Newspaper $newspaper = null;

    /**
     * Author relation.
     *
     * @var User|null Author
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $author = null;

    /**
     * Getter for id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for content.
     *
     * @return string|null Content
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Setter for content.
     *
     * @param string $content Content
     *
     * @return $this Content
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Getter for newspaper.
     *
     * @return Newspaper|null Newspaper
     */
    public function getNewspaper(): ?Newspaper
    {
        return $this->newspaper;
    }

    /**
     * Setter for newspaper.
     *
     * @param Newspaper|null $newspaper Newspaper
     *
     * @return $this Newspaper
     */
    public function setNewspaper(?Newspaper $newspaper): static
    {
        $this->newspaper = $newspaper;

        return $this;
    }

    /**
     * Getter for author.
     *
     * @return User|null Author
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Setter for author.
     *
     * @param User|null $author Author
     *
     * @return $this Author
     */
    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Getter for createdAt.
     *
     * @return \DateTimeImmutable|null created At
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for createdAt.
     *
     * @param \DateTimeImmutable $createdAt createdAt
     *
     * @return $this createdAt
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
