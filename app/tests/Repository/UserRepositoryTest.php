<?php

/**
 * User repository tests.
 */

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Class UserRepositoryTest.
 */
class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    /**
     * Set up test.
     */
    protected function setUp(): void
    {
        static::bootKernel();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * Test upgrade password.
     */
    public function testUpgradePassword(): void
    {
        // given
        $user = new User();
        $user->setEmail('repository@example.com');
        $user->setPassword('old-password');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // when
        $this->userRepository->upgradePassword($user, 'new-hashed-password');

        // then
        self::assertSame('new-hashed-password', $user->getPassword());
    }

    /**
     * Test upgrade password rejects unsupported user.
     */
    public function testUpgradePasswordRejectsUnsupportedUser(): void
    {
        // given
        $user = $this->createMock(PasswordAuthenticatedUserInterface::class);

        // then
        $this->expectException(UnsupportedUserException::class);

        // when
        $this->userRepository->upgradePassword($user, 'new-hashed-password');
    }
}
