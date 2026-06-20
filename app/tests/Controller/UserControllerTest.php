<?php

/**
 * User controller tests.
 */

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserControllerTest.
 */
class UserControllerTest extends WebTestCase
{
    private KernelBrowser $httpClient;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Set up test.
     */
    protected function setUp(): void
    {
        $this->httpClient = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    /**
     * Test login page.
     */
    public function testLoginPage(): void
    {
        // given
        $expectedHeading = 'Zaloguj się';

        // when
        $this->httpClient->request('GET', '/login');

        // then
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $expectedHeading);
    }

    /**
     * Test logout method.
     */
    public function testLogoutMethodThrowsLogicException(): void
    {
        // given
        $controller = new SecurityController();

        // then
        $this->expectException(\LogicException::class);

        // when
        $controller->logout();
    }

    /**
     * Test register.
     */
    public function testRegister(): void
    {
        // given
        $crawler = $this->httpClient->request('GET', '/register');
        $form = $crawler->filter('form')->first()->form([
            'registration_form[email]' => 'registered@example.com',
            'registration_form[password][first]' => 'password123',
            'registration_form[password][second]' => 'password123',
        ]);

        // when
        $this->httpClient->submit($form);

        // then
        self::assertResponseRedirects('/login');
        $user = $this->getEntityManager()->getRepository(User::class)
            ->findOneBy(['email' => 'registered@example.com']);
        self::assertInstanceOf(User::class, $user);
    }

    /**
     * Test profile index.
     */
    public function testProfileIndex(): void
    {
        // given
        $this->httpClient->loginUser($this->createUser('profile@example.com'));

        // when
        $this->httpClient->request('GET', '/profile');

        // then
        self::assertResponseIsSuccessful();
    }

    /**
     * Test profile edit.
     */
    public function testProfileEdit(): void
    {
        // given
        $user = $this->createUser('before-profile-edit@example.com');
        $this->httpClient->loginUser($user);
        $crawler = $this->httpClient->request('GET', '/profile/edit');
        $form = $crawler->filter('form')->form([
            'user_edit[email]' => 'after-profile-edit@example.com',
        ]);

        // when
        $this->httpClient->submit($form);

        // then
        self::assertResponseRedirects('/profile/edit');
    }

    /**
     * Test profile password change.
     */
    public function testProfilePasswordChange(): void
    {
        // given
        $user = $this->createUser('password-profile@example.com', 'current-password');
        $this->httpClient->loginUser($user);
        $crawler = $this->httpClient->request('GET', '/profile/change-password');
        $form = $crawler->filter('form')->form([
            'user_password[currentPassword]' => 'current-password',
            'user_password[newPassword]' => 'new-password',
            'user_password[confirmNewPassword]' => 'new-password',
        ]);

        // when
        $this->httpClient->submit($form);

        // then
        self::assertResponseRedirects('/profile/change-password');
    }

    /**
     * Test invalid profile password change.
     */
    public function testProfilePasswordChangeWithInvalidPassword(): void
    {
        // given
        $user = $this->createUser('invalid-password-profile@example.com');
        $this->httpClient->loginUser($user);
        $crawler = $this->httpClient->request('GET', '/profile/change-password');
        $form = $crawler->filter('form')->form([
            'user_password[currentPassword]' => 'invalid-password',
            'user_password[newPassword]' => 'new-password',
            'user_password[confirmNewPassword]' => 'new-password',
        ]);

        // when
        $this->httpClient->submit($form);

        // then
        self::assertResponseIsSuccessful();
    }

    /**
     * Test administrator index and edit.
     */
    public function testAdminIndexAndEdit(): void
    {
        // given
        $admin = $this->createUser('admin@example.com', roles: ['ROLE_ADMIN']);
        $editedUser = $this->createUser('before-admin-edit@example.com');
        $this->httpClient->loginUser($admin);
        $this->httpClient->request('GET', '/users');
        self::assertResponseIsSuccessful();
        $crawler = $this->httpClient->request('GET', '/users/edit/'.$editedUser->getId());
        $form = $crawler->filter('form')->form([
            'admin_edit[email]' => 'after-admin-edit@example.com',
        ]);

        // when
        $this->httpClient->submit($form);

        // then
        self::assertResponseRedirects('/users');
    }

    /**
     * Test administrator password change.
     */
    public function testAdminChangesPassword(): void
    {
        // given
        $admin = $this->createUser('password-admin@example.com', roles: ['ROLE_ADMIN']);
        $user = $this->createUser('changed-password@example.com');
        $this->httpClient->loginUser($admin);
        $crawler = $this->httpClient->request('GET', '/users/change-password/'.$user->getId());
        $form = $crawler->filter('form')->form([
            'admin_password[newPasswordAdmin]' => 'new-password',
            'admin_password[confirmNewPasswordAdmin]' => 'new-password',
        ]);

        // when
        $this->httpClient->submit($form);

        // then
        self::assertResponseRedirects('/users');
    }

    /**
     * Test administrator cannot delete self.
     */
    public function testAdminCannotDeleteSelf(): void
    {
        // given
        $admin = $this->createUser('self-delete-admin@example.com', roles: ['ROLE_ADMIN']);
        $this->httpClient->loginUser($admin);

        // when
        $this->httpClient->request('GET', '/users/delete/'.$admin->getId());

        // then
        self::assertResponseRedirects('/users');
    }

    /**
     * Test administrator deletes user.
     */
    public function testAdminDeletesUser(): void
    {
        // given
        $admin = $this->createUser('delete-admin@example.com', roles: ['ROLE_ADMIN']);
        $user = $this->createUser('deleted-user@example.com');
        $userId = $user->getId();
        $this->httpClient->loginUser($admin);
        $crawler = $this->httpClient->request('GET', '/users/delete/'.$userId);
        $form = $crawler->filter('form')->form();

        // when
        $this->httpClient->submit($form);

        // then
        self::assertResponseRedirects('/users');
        self::assertNull($this->getEntityManager()->getRepository(User::class)->find($userId));
    }

    /**
     * Create user.
     *
     * @param string        $email         Email
     * @param string        $plainPassword Plain password
     * @param array<string> $roles         Roles
     *
     * @return User User
     */
    private function createUser(string $email, string $plainPassword = 'password123', array $roles = ['ROLE_USER']): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Get entity manager.
     *
     * @return EntityManagerInterface Entity manager
     */
    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
