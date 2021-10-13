<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Service;

use Dbp\Relay\AuthBundle\API\UserRolesInterface;

class DefaultUserRoles implements UserRolesInterface
{
    /**
     * The default implementation converts OAuth2 scopes to Symfony roles
     * by prefixing them with "ROLE_SCOPE_" and converting to uppercase.
     */
    public function getRoles(?string $userIdentifier, array $scopes): array
    {
        $roles = [];
        foreach ($scopes as $scope) {
            $roles[] = 'ROLE_SCOPE_'.mb_strtoupper($scope);
        }

        return $roles;
    }
}
