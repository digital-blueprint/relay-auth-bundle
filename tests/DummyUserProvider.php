<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Tests;

use Dbp\Relay\AuthBundle\Authenticator\BearerUserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

class DummyUserProvider implements BearerUserProviderInterface
{
    private $user;
    private $token;

    public function __construct(UserInterface $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function loadUserByToken(string $accessToken): UserInterface
    {
        if ($this->token !== $accessToken) {
            throw new AuthenticationException('invalid token');
        }

        return $this->user;
    }

    public function loadUserByValidatedToken(array $jwt): UserInterface
    {
        return $this->user;
    }
}
