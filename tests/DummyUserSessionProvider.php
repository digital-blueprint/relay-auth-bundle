<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Tests;

use Dbp\Relay\AuthBundle\Authenticator\OIDCUserSessionProviderInterface;

class DummyUserSessionProvider implements OIDCUserSessionProviderInterface
{
    /** @var string|null */
    private $id;

    /** @var array */
    private $scopes;

    public function __construct(?string $id = 'id', array $scopes = [])
    {
        $this->id = $id;
        $this->scopes = $scopes;
    }

    public function setSessionToken(?array $jwt): void
    {
    }

    public function getScopes(): array
    {
        return $this->scopes;
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
