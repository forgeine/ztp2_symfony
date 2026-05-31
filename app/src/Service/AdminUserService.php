<?php
/**
 * AdminUserService.
 */

namespace App\Service;

use App\Entity\Newspaper;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\NewspaperRepository;
use App\Repository\RatingRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class AdminUserService.
 */
class AdminUserService implements AdminUserServiceInterface
{
    /**
     * Constructor.
     *
     * @param UserRepository              $userRepository      User Repository
     * @param NewspaperRepository         $newspaperRepository Newspaper Repository
     * @param CommentRepository           $commentRepository   Comment Repository
     * @param RatingRepository            $ratingRepository    Rating Repository
     * @param UserPasswordHasherInterface $passwordHasher      Password Hasher
     * @param EntityManagerInterface      $em                  Entity Manager
     */
    public function __construct(private readonly UserRepository $userRepository, private readonly NewspaperRepository $newspaperRepository, private readonly CommentRepository $commentRepository, private readonly RatingRepository $ratingRepository, private readonly UserPasswordHasherInterface $passwordHasher, private readonly EntityManagerInterface $em)
    {
    }

    /**
     * getAllUsers.
     *
     * @return array All users
     */
    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    /**
     * updateUser.
     *
     * @param User $user Entity User
     *
     * @return void Void
     */
    public function updateUser(User $user): void
    {
        $this->em->flush();
    }

    /**
     * changePassword.
     *
     * @param User   $user        Entity User
     * @param string $newPassword New password
     *
     * @return void Void
     */
    public function changePassword(User $user, string $newPassword): void
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
        $this->em->flush();
    }

    /**
     * deleteUser.
     *
     * @param User $user Entity User
     *
     * @return void Void
     */
    public function deleteUser(User $user): void
    {
        foreach ($this->commentRepository->findBy(['author' => $user]) as $comment) {
            $this->em->remove($comment);
        }

        foreach ($this->ratingRepository->findBy(['user' => $user]) as $rating) {
            $newspaper = $rating->getNewspaper();
            $this->em->remove($rating);

            if ($newspaper instanceof Newspaper && $newspaper->getAuthor() !== $user) {
                $this->recalculateAverageRatingWithoutUser($newspaper, $user);
            }
        }

        $newspapers = $this->newspaperRepository->findByAuthor($user);
        foreach ($newspapers as $newspaper) {
            $this->em->remove($newspaper);
        }
        $this->em->remove($user);
        $this->em->flush();
    }

    /**
     * Recalculate average rating after removing user ratings.
     *
     * @param Newspaper $newspaper Newspaper entity
     * @param User      $user      Entity User
     *
     * @return void Void
     */
    private function recalculateAverageRatingWithoutUser(Newspaper $newspaper, User $user): void
    {
        $sum = 0;
        $count = 0;
        foreach ($newspaper->getRatings() as $rating) {
            if ($rating->getUser() === $user) {
                continue;
            }
            $sum += $rating->getValue();
            ++$count;
        }

        $newspaper->setAverageRating($count > 0 ? round($sum / $count, 2) : 0);
    }
}
