<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Tests;

use Dbp\Relay\AuthBundle\Authenticator\OIDCUserSessionProviderInterface;

class DummyUserSessionProvider implements OIDCUserSessionProviderInterface
{
    private $id;

    public function __construct(?string $id = 'id')
    {
        $this->id = $id;
    }

    public function setSessionToken(?array $jwt): void
    {
    }

    public function getUserIdentifier(): ?string
    {
        return $this->id;
    }

    public function getSessionLoggingId(): ?string
    {
        return 'logging-id';
    }

    public function getSessionCacheKey(): ?string
    {
        return 'cache';
    }

    public function getSessionTTL(): int
    {
        return 42;
    }
}
