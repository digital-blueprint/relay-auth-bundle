<?php

declare(strict_types=1);

namespace DBP\API\KeycloakBundle\Keycloak;

use Symfony\Component\Security\Core\User\UserInterface;

interface KeycloakBearerUserProviderInterface
{
    public function loadUserByToken(string $accessToken): UserInterface;

    public function loadUserByValidatedToken(array $jwt): UserInterface;
}
