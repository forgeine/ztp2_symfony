<?php

/**
 * User form type tests.
 */

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\Type\AdminEditType;
use App\Form\Type\AdminPasswordType;
use App\Form\Type\RegistrationFormType;
use App\Form\Type\UserEditType;
use App\Form\Type\UserPasswordType;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class UserFormTypesTest.
 */
class UserFormTypesTest extends TypeTestCase
{
    /**
     * Test user data form.
     *
     * @param string $type Form type
     */
    #[DataProvider('userDataFormProvider')]
    public function testUserDataForm(string $type): void
    {
        // given
        $user = new User();
        $form = $this->factory->create($type, $user);

        // when
        $form->submit(['email' => 'test@example.com']);

        // then
        self::assertTrue($form->isSynchronized());
        self::assertSame(User::class, $form->getConfig()->getDataClass());
        self::assertTrue($form->has('email'));
    }

    /**
     * Test registration form.
     */
    public function testRegistrationForm(): void
    {
        // given
        $user = new User();
        $form = $this->factory->create(RegistrationFormType::class, $user);

        // when
        $form->submit([
            'email' => 'registered@example.com',
            'password' => ['first' => 'password', 'second' => 'password'],
        ]);

        // then
        self::assertTrue($form->isSynchronized());
        self::assertSame('registered@example.com', $user->getEmail());
        self::assertSame('password', $user->getPassword());
    }

    /**
     * Test password form.
     *
     * @param string                $type Form type
     * @param array<string, string> $data Form data
     */
    #[DataProvider('passwordFormProvider')]
    public function testPasswordForm(string $type, array $data): void
    {
        // given
        $form = $this->factory->create($type);

        // when
        $form->submit($data);

        // then
        self::assertTrue($form->isSynchronized());
        self::assertSame($data, array_intersect_key($form->getData(), $data));
    }

    /**
     * @return array<string, array{class-string, array<string, string>}>
     */
    public static function passwordFormProvider(): array
    {
        return [
            'user password' => [
                UserPasswordType::class,
                [
                    'currentPassword' => 'current',
                    'newPassword' => 'new-password',
                    'confirmNewPassword' => 'new-password',
                ],
            ],
            'admin password' => [
                AdminPasswordType::class,
                [
                    'newPasswordAdmin' => 'new-password',
                    'confirmNewPasswordAdmin' => 'new-password',
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{class-string}>
     */
    public static function userDataFormProvider(): array
    {
        return [
            'user edit' => [UserEditType::class],
            'admin edit' => [AdminEditType::class],
        ];
    }
}
