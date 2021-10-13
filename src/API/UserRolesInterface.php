<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\API;

interface UserRolesInterface
{
    /**
     * @param string[] $scopes
     *
     * @return string[]
     */
    public function getRoles(?string $userIdentifier, array $scopes): array;
}
