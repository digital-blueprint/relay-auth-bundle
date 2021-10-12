<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Authenticator;

use Symfony\Component\Security\Core\User\UserInterface;

interface BearerUserProviderInterface
{
    public function loadUserByToken(string $accessToken): UserInterface;

    public function loadUserByValidatedToken(array $jwt): UserInterface;
}
