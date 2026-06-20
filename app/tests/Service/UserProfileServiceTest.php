<?php

/**
 * User profile service tests.
 */

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserProfileService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserProfileServiceTest.
 */
class UserProfileServiceTest extends TestCase
{
    /**
     * Test update user.
     */
    public function testUpdateUser(): void
    {
        // given
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');
        $service = new UserProfileService(
            $this->createMock(UserPasswordHasherInterface::class),
            $entityManager
        );

        // when
        $service->updateUser(new User());

        // then
        self::addToAssertionCount(1);
    }

    /**
     * Test validate and change password.
     */
    public function testValidateAndChangePassword(): void
    {
        // given
        $user = new User();
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('isPasswordValid')->with($user, 'current')->willReturn(true);
        $passwordHasher->method('hashPassword')->with($user, 'new-password')->willReturn('hashed');
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');
        $service = new UserProfileService($passwordHasher, $entityManager);

        // when
        $result = $service->validateAndChangePassword(
            $user,
            'current',
            'new-password',
            'new-password'
        );

        // then
        self::assertTrue($result);
        self::assertSame('hashed', $user->getPassword());
    }

    /**
     * Test invalid current password.
     */
    public function testDoesNotChangePasswordForInvalidCurrentPassword(): void
    {
        // given
        $user = new User();
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('isPasswordValid')->willReturn(false);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('flush');
        $service = new UserProfileService($passwordHasher, $entityManager);

        // when
        $result = $service->validateAndChangePassword($user, 'invalid', 'new', 'new');

        // then
        self::assertFalse($result);
    }

    /**
     * Test different password confirmation.
     */
    public function testDoesNotChangePasswordWhenConfirmationDiffers(): void
    {
        // given
        $user = new User();
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('isPasswordValid')->willReturn(true);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('flush');
        $service = new UserProfileService($passwordHasher, $entityManager);

        // when
        $result = $service->validateAndChangePassword($user, 'current', 'new', 'different');

        // then
        self::assertFalse($result);
    }
}
