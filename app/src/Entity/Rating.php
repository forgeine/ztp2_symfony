<?php
/**
 * Rating.
 */

namespace App\Entity;

use App\Repository\RatingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Rating.
 */
#[ORM\Entity(repositoryClass: RatingRepository::class)]
#[ORM\Table(name: 'ratings')]
class Rating
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Value.
     */
    #[ORM\Column(type: 'integer')]
    private ?int $value = null;

    /**
     * Newspaper relation.
     */
    #[ORM\ManyToOne(targetEntity: Newspaper::class, inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Newspaper $newspaper = null;

    /**
     * User relation.
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;
    // Getters and setters

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
     * Getter for value.
     *
     * @return int|null Value
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * Setter for value.
     *
     * @param int $value Value
     *
     * @return $this Value
     */
    public function setValue(int $value): self
    {
        $this->value = $value;

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
    public function setNewspaper(?Newspaper $newspaper): self
    {
        $this->newspaper = $newspaper;

        return $this;
    }

    /**
     * Getter for user.
     *
     * @return User|null User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Setter for user.
     *
     * @param User|null $user User
     *
     * @return $this User
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
