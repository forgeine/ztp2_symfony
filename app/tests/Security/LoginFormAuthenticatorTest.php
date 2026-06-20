<?php

/**
 * Login form authenticator tests.
 */

namespace App\Tests\Security;

use App\Security\LoginFormAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * Class LoginFormAuthenticatorTest.
 */
class LoginFormAuthenticatorTest extends TestCase
{
    /**
     * Test support for login POST request.
     */
    public function testSupportsLoginPostRequest(): void
    {
        // given
        $authenticator = $this->createAuthenticator();
        $loginRequest = Request::create('/login', 'POST');
        $loginRequest->attributes->set('_route', 'app_login');
        $getRequest = Request::create('/login', 'GET');
        $getRequest->attributes->set('_route', 'app_login');

        // when
        $supportsPost = $authenticator->supports($loginRequest);
        $supportsGet = $authenticator->supports($getRequest);

        // then
        self::assertTrue($supportsPost);
        self::assertFalse($supportsGet);
    }

    /**
     * Test authenticate.
     */
    public function testAuthenticate(): void
    {
        // given
        $request = Request::create('/login', 'POST', [
            'email' => 'user@example.com',
            'password' => 'password',
            '_csrf_token' => 'token',
        ]);
        $request->setSession(new Session(new MockArraySessionStorage()));
        $authenticator = $this->createAuthenticator();

        // when
        $passport = $authenticator->authenticate($request);

        // then
        $userBadge = $passport->getBadge(UserBadge::class);
        self::assertInstanceOf(UserBadge::class, $userBadge);
        self::assertSame('user@example.com', $userBadge->getUserIdentifier());
    }

    /**
     * Test successful authentication target path redirect.
     */
    public function testAuthenticationSuccessRedirectsToTargetPath(): void
    {
        // given
        $request = Request::create('/login');
        $session = new Session(new MockArraySessionStorage());
        $session->set('_security.main.target_path', '/profile');
        $request->setSession($session);
        $authenticator = $this->createAuthenticator();

        // when
        $response = $authenticator->onAuthenticationSuccess(
            $request,
            $this->createMock(TokenInterface::class),
            'main'
        );

        // then
        self::assertSame('/profile', $response?->getTargetUrl());
    }

    /**
     * Test successful authentication default redirect.
     */
    public function testAuthenticationSuccessRedirectsToDefaultRoute(): void
    {
        // given
        $request = Request::create('/login');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->with('newspaper_index')->willReturn('/newspaper');
        $authenticator = new LoginFormAuthenticator($urlGenerator);

        // when
        $response = $authenticator->onAuthenticationSuccess(
            $request,
            $this->createMock(TokenInterface::class),
            'main'
        );

        // then
        self::assertSame('/newspaper', $response?->getTargetUrl());
    }

    /**
     * Test login URL.
     */
    public function testGetLoginUrl(): void
    {
        // given
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->with('app_login')->willReturn('/login');
        $authenticator = new LoginFormAuthenticator($urlGenerator);
        $method = new \ReflectionMethod(LoginFormAuthenticator::class, 'getLoginUrl');

        // when
        $result = $method->invoke($authenticator, Request::create('/'));

        // then
        self::assertSame('/login', $result);
    }

    /**
     * Create authenticator.
     *
     * @return LoginFormAuthenticator Authenticator
     */
    private function createAuthenticator(): LoginFormAuthenticator
    {
        return new LoginFormAuthenticator($this->createMock(UrlGeneratorInterface::class));
    }
}
