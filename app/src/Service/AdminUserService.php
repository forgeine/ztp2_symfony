<?php
/**
 * AdminUserService.
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\NewspaperRepository;
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
     * @param UserRepository              $userRepository   User Repository
     * @param NewspaperRepository            $newspaperRepository Newspaper Repository
     * @param UserPasswordHasherInterface $passwordHasher   Password Hasher
     * @param EntityManagerInterface      $em               Entity Manager
     */
    public function __construct(private readonly UserRepository $userRepository, private readonly NewspaperRepository $newspaperRepository, private readonly UserPasswordHasherInterface $passwordHasher, private readonly EntityManagerInterface $em)
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
        $newspapers = $this->newspaperRepository->findByAuthor($user);
        foreach ($newspapers as $newspaper) {
            $this->em->remove($newspaper);
        }
        $this->em->remove($user);
        $this->em->flush();
    }
}
