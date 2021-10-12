<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Tests\Authenticator;

use Dbp\Relay\AuthBundle\Authenticator\BearerUser;
use PHPUnit\Framework\TestCase;

class BearerUserTest extends TestCase
{
    public function testRolesWithNoRealUser()
    {
        $user = new BearerUser(null, ['foobar']);
        $this->assertSame(['foobar'], $user->getRoles());
    }

    public function testGetUserIdentifier()
    {
        $user = new BearerUser(null, ['foobar']);
        $this->assertSame('', $user->getUserIdentifier());
        $this->assertSame('', $user->getUsername());
        $user = new BearerUser('quux', ['foobar']);
        $this->assertSame('quux', $user->getUserIdentifier());
        $this->assertSame('quux', $user->getUsername());
    }
}
