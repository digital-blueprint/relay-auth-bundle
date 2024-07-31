<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Tests\Authenticator;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Dbp\Relay\AuthBundle\Authenticator\BearerAuthenticator;
use Dbp\Relay\AuthBundle\Authenticator\BearerUser;
use Dbp\Relay\AuthBundle\Tests\DummyUserProvider;
use Dbp\Relay\AuthBundle\Tests\UserSession\DummyUserSessionProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class BearerAuthenticatorTest extends ApiTestCase
{
    public function testAuthenticateNoHeader()
    {
        $user = new BearerUser('foo', ['role']);
        $provider = new DummyUserProvider($user, 'nope');
        $auth = new BearerAuthenticator(new DummyUserSessionProvider(), $provider);

        $req = new Request();
        $this->expectException(BadCredentialsException::class);
        $auth->authenticate($req);
    }

    public function testAuthenticate()
    {
        $user = new BearerUser('foo', ['role']);
        $provider = new DummyUserProvider($user, 'nope');
        $auth = new BearerAuthenticator(new DummyUserSessionProvider(), $provider);

        $req = new Request();
        $req->headers->set('Authorization', 'Bearer nope');
        $passport = $auth->authenticate($req);
        $badge = $passport->getBadge(UserBadge::class);
        assert($badge instanceof UserBadge);
        $this->assertSame('foo', $badge->getUser()->getUserIdentifier());
    }

    public function testSupports()
    {
        $user = new BearerUser('foo', ['role']);
        $provider = new DummyUserProvider($user, 'bar');
        $auth = new BearerAuthenticator(new DummyUserSessionProvider(), $provider);

        $this->assertFalse($auth->supports(new Request()));

        $r = new Request();
        $r->headers->set('Authorization', 'foobar');
        $this->assertTrue($auth->supports($r));
    }

    public function testOnAuthenticationSuccess()
    {
        $user = new BearerUser('foo', ['role']);
        $provider = new DummyUserProvider($user, 'bar');
        $auth = new BearerAuthenticator(new DummyUserSessionProvider(), $provider);
        $response = $auth->onAuthenticationSuccess(new Request(), new NullToken(), 'firewall');
        $this->assertNull($response);
    }

    public function testOnAuthenticationFailure()
    {
        $user = new BearerUser('foo', ['role']);
        $provider = new DummyUserProvider($user, 'bar');
        $auth = new BearerAuthenticator(new DummyUserSessionProvider(), $provider);
        $response = $auth->onAuthenticationFailure(new Request(), new AuthenticationException());
        $this->assertSame(401, $response->getStatusCode());
        $this->assertNotNull(json_decode($response->getContent()));
    }
}
