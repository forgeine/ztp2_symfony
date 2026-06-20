<?php

/**
 * Registration service tests.
 */

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RegistrationServiceTest.
 */
class RegistrationServiceTest extends TestCase
{
    /**
     * Test register user.
     */
    public function testRegisterUser(): void
    {
        // given
        $user = new User();
        $user->setPassword('plain-password');
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects(self::once())
            ->method('hashPassword')
            ->with($user, 'plain-password')
            ->willReturn('hashed-password');
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with($user);
        $entityManager->expects(self::once())->method('flush');
        $service = new RegistrationService(
            $passwordHasher,
            $this->createMock(TranslatorInterface::class),
            $entityManager
        );

        // when
        $service->registerUser($user);

        // then
        self::assertSame('hashed-password', $user->getPassword());
        self::assertContains('ROLE_USER', $user->getRoles());
    }

    /**
     * Test success message.
     */
    public function testGetSuccessMessage(): void
    {
        // given
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())
            ->method('trans')
            ->with('message.registration_successful')
            ->willReturn('Registered');
        $service = new RegistrationService(
            $this->createMock(UserPasswordHasherInterface::class),
            $translator,
            $this->createMock(EntityManagerInterface::class)
        );

        // when
        $result = $service->getSuccessMessage();

        // then
        self::assertSame('Registered', $result);
    }
}
