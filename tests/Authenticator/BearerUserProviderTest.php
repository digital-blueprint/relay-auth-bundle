<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Tests\Authenticator;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Dbp\Relay\AuthBundle\Authenticator\BearerUserProvider;
use Dbp\Relay\AuthBundle\OIDC\OIDProvider;
use Dbp\Relay\AuthBundle\Service\DefaultUserRoles;
use Dbp\Relay\AuthBundle\Tests\DummyUserSessionProvider;
use Psr\Log\NullLogger;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class BearerUserProviderTest extends ApiTestCase
{
    public function testWithIdentifier()
    {
        $oid = new OIDProvider();
        $udprov = new DummyUserSessionProvider('foo');
        $prov = new BearerUserProvider($udprov, $oid, new DefaultUserRoles());
        $user = $prov->loadUserByValidatedToken([]);
        $this->assertSame('foo', $user->getUserIdentifier());
        $this->assertSame([], $user->getRoles());
    }

    public function testWithoutIdentifier()
    {
        $oid = new OIDProvider();
        $udprov = new DummyUserSessionProvider(null);
        $prov = new BearerUserProvider($udprov, $oid, new DefaultUserRoles());
        $user = $prov->loadUserByValidatedToken([]);
        $this->assertSame('', $user->getUserIdentifier());
        $this->assertSame([], $user->getRoles());
    }

    public function testInvalidTokenLocal()
    {
        $oid = new OIDProvider();
        $udprov = new DummyUserSessionProvider('foo');
        $prov = new BearerUserProvider($udprov, $oid, new DefaultUserRoles());
        $prov->setLogger(new NullLogger());
        $prov->setConfig([
            'remote_validation' => false,
            'local_validation_leeway' => 0,
        ]);
        $this->expectException(AuthenticationException::class);
        $prov->loadUserByToken('mytoken');
    }
}
