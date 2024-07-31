<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Tests\UserSession;

use Dbp\Relay\AuthBundle\UserSession\OIDCUserSessionProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class UserSessionTest extends TestCase
{
    public function testIsServiceAccountToken()
    {
        $this->assertTrue(OIDCUserSessionProvider::isServiceAccountToken(['scope' => 'foo bar']));
        $this->assertFalse(OIDCUserSessionProvider::isServiceAccountToken(['scope' => 'openid foo bar']));
        $this->assertFalse(OIDCUserSessionProvider::isServiceAccountToken(['scope' => 'openid']));
        $this->assertFalse(OIDCUserSessionProvider::isServiceAccountToken(['scope' => 'foo openid bar']));
        $this->assertFalse(OIDCUserSessionProvider::isServiceAccountToken(['scope' => 'foo bar openid']));
    }

    public function testGetLoggingId()
    {
        $session = new OIDCUserSessionProvider(new ParameterBag());

        $session->setSessionToken([]);
        $this->assertSame('unknown-unknown', $session->getSessionLoggingId());
        $session->setSessionToken(['azp' => 'clientA', 'session_state' => 'state']);
        $this->assertSame('clientA-abfa50', $session->getSessionLoggingId());
    }

    public function testGetSessionCacheKey()
    {
        $session = new OIDCUserSessionProvider(new ParameterBag());
        $session->setSessionToken(['scope' => 'foo']);
        $old = $session->getSessionCacheKey();
        $session->setSessionToken(['scope' => 'bar']);
        $new = $session->getSessionCacheKey();
        $this->assertNotSame($old, $new);
    }

    public function testGetSessionTTL()
    {
        $session = new OIDCUserSessionProvider(new ParameterBag());
        $session->setSessionToken([]);
        $this->assertSame(-1, $session->getSessionTTL());

        $session->setSessionToken(['exp' => 42, 'iat' => 24]);
        $this->assertSame(18, $session->getSessionTTL());
    }
}
