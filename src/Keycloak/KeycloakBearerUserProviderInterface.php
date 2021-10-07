<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Keycloak;

use Symfony\Component\Security\Core\User\UserInterface;

interface KeycloakBearerUserProviderInterface
{
    public function loadUserByToken(string $accessToken): UserInterface;

    public function loadUserByValidatedToken(array $jwt): UserInterface;
}
