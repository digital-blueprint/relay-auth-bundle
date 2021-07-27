<?php

declare(strict_types=1);

namespace DBP\API\KeycloakBundle\Tests\Keycloak;

use DBP\API\KeycloakBundle\Service\KeycloakUserSession;
use PHPUnit\Framework\TestCase;

class KeycloakUserSessionTest extends TestCase
{
    public function testIsServiceAccountToken()
    {
        $this->assertTrue(KeycloakUserSession::isServiceAccountToken(['scope' => 'foo bar']));
        $this->assertFalse(KeycloakUserSession::isServiceAccountToken(['scope' => 'openid foo bar']));
        $this->assertFalse(KeycloakUserSession::isServiceAccountToken(['scope' => 'openid']));
        $this->assertFalse(KeycloakUserSession::isServiceAccountToken(['scope' => 'foo openid bar']));
        $this->assertFalse(KeycloakUserSession::isServiceAccountToken(['scope' => 'foo bar openid']));
    }

    public function testGetLoggingId()
    {
        $session = new KeycloakUserSession();
        $session->setSessionToken([]);
        $this->assertSame('unknown-unknown', $session->getSessionLoggingId());
        $session->setSessionToken(['azp' => 'clientA', 'session_state' => 'state']);
        $this->assertSame('clientA-abfa50', $session->getSessionLoggingId());
    }

    public function testGetUserRoles()
    {
        $session = new KeycloakUserSession();
        $session->setSessionToken([]);
        $this->assertSame([], $session->getUserRoles());
        $session->setSessionToken(['scope' => 'foo bar quux-buz a_b']);
        $this->assertSame(
            ['ROLE_SCOPE_FOO', 'ROLE_SCOPE_BAR', 'ROLE_SCOPE_QUUX-BUZ', 'ROLE_SCOPE_A_B'],
            $session->getUserRoles());
    }

    public function testGetSessionCacheKey()
    {
        $session = new KeycloakUserSession();
        $session->setSessionToken(['scope' => 'foo']);
        $old = $session->getSessionCacheKey();
        $session->setSessionToken(['scope' => 'bar']);
        $new = $session->getSessionCacheKey();
        $this->assertNotSame($old, $new);
    }

    public function testGetSessionTTL()
    {
        $session = new KeycloakUserSession();
        $session->setSessionToken([]);
        $this->assertSame(-1, $session->getSessionTTL());

        $session->setSessionToken(['exp' => 42, 'iat' => 24]);
        $this->assertSame(18, $session->getSessionTTL());
    }
}
