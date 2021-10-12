<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Tests\Authenticator;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Dbp\Relay\AuthBundle\Authenticator\BearerUserProvider;
use Dbp\Relay\AuthBundle\OIDC\OIDProvider;
use Dbp\Relay\AuthBundle\Tests\DummyUserSession;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class BearerUserProviderTest extends ApiTestCase
{
    public function testWithIdentifier()
    {
        $oid = new OIDProvider();
        $udprov = new DummyUserSession('foo', ['role']);
        $prov = new BearerUserProvider($udprov, $oid);
        $user = $prov->loadUserByValidatedToken([]);
        $this->assertSame('foo', $user->getUserIdentifier());
        $this->assertSame(['role'], $user->getRoles());
    }

    public function testWithoutIdentifier()
    {
        $oid = new OIDProvider();
        $udprov = new DummyUserSession(null, ['role']);
        $prov = new BearerUserProvider($udprov, $oid);
        $user = $prov->loadUserByValidatedToken([]);
        $this->assertSame('', $user->getUserIdentifier());
        $this->assertSame(['role'], $user->getRoles());
    }

    public function testInvalidTokenLocal()
    {
        $oid = new OIDProvider();
        $udprov = new DummyUserSession('foo', ['role']);
        $prov = new BearerUserProvider($udprov, $oid);
        $prov->setConfig([
            'remote_validation' => false,
            'local_validation_leeway' => 0,
        ]);
        $this->expectException(AuthenticationException::class);
        $prov->loadUserByToken('mytoken');
    }
}
