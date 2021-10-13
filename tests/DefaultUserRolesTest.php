<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Tests;

use Dbp\Relay\AuthBundle\Service\DefaultUserRoles;
use PHPUnit\Framework\TestCase;

class DefaultUserRolesTest extends TestCase
{
    public function testGetRoles()
    {
        $userRoles = new DefaultUserRoles();
        $roles = $userRoles->getRoles(null, ['foo-bar']);
        $this->assertSame(['ROLE_SCOPE_FOO-BAR'], $roles);
    }
}
