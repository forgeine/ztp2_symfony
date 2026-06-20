<?php

/**
 * Category voter tests.
 */

namespace App\Tests\Security\Voter;

use App\Entity\Category;
use App\Entity\User;
use App\Security\Voter\CategoryVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class CategoryVoterTest.
 */
class CategoryVoterTest extends TestCase
{
    /**
     * Test regular user access.
     *
     * @param string $attribute Attribute
     */
    #[DataProvider('regularUserAttributesProvider')]
    public function testRegularUserIsDenied(string $attribute): void
    {
        // given
        $user = new User();
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $voter = new CategoryVoter();

        // when
        $result = $voter->vote($token, new Category(), [$attribute]);

        // then
        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    /**
     * Test administrator access.
     */
    public function testAdministratorIsGranted(): void
    {
        // given
        $admin = new User();
        $admin->setRoles(['ROLE_ADMIN']);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($admin);
        $voter = new CategoryVoter();

        // when
        $result = $voter->vote($token, new Category(), ['EDIT']);

        // then
        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * Test anonymous user access.
     */
    public function testAnonymousUserIsDenied(): void
    {
        // given
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);
        $voter = new CategoryVoter();

        // when
        $result = $voter->vote($token, new Category(), ['VIEW']);

        // then
        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    /**
     * Test unsupported vote.
     */
    public function testUnsupportedVoteIsAbstained(): void
    {
        // given
        $token = $this->createMock(TokenInterface::class);
        $voter = new CategoryVoter();

        // when
        $unsupportedAttribute = $voter->vote($token, new Category(), ['UNKNOWN']);
        $unsupportedSubject = $voter->vote($token, new \stdClass(), ['EDIT']);

        // then
        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $unsupportedAttribute);
        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $unsupportedSubject);
    }

    /**
     * Test view access.
     */
    public function testCanView(): void
    {
        // given
        $method = new \ReflectionMethod(CategoryVoter::class, 'canView');

        // when
        $result = $method->invoke(new CategoryVoter());

        // then
        self::assertTrue($result);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function regularUserAttributesProvider(): array
    {
        return [
            'edit' => ['EDIT'],
            'delete' => ['DELETE'],
            'create' => ['CREATE'],
            'view' => ['VIEW'],
        ];
    }
}
