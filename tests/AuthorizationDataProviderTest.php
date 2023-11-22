<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Tests;

use Dbp\Relay\AuthBundle\Service\AuthorizationDataProvider;
use PHPUnit\Framework\TestCase;

class AuthorizationDataProviderTest extends TestCase
{
    /**
     * @var AuthorizationDataProvider
     */
    private $authorizationDataProvider;

    public function testGetAvailableAttributes(): void
    {
        $this->setUpUserSession([]);

        $this->assertEquals(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_WRITER'], $this->authorizationDataProvider->getAvailableAttributes());
    }

    public function testUserAttributesDeprecatedScopeAttribute(): void
    {
        // NOTE: user identifier is not required
        $this->setUpUserSession(['__', 'user']);

        $this->assertEquals(true, $this->authorizationDataProvider->getUserAttributes('username')['ROLE_USER']);
        $this->assertEquals(false, $this->authorizationDataProvider->getUserAttributes(null)['ROLE_ADMIN']);

        $this->setUpUserSession(['admin', '_', '__']);

        $this->assertEquals(false, $this->authorizationDataProvider->getUserAttributes('username')['ROLE_USER']);
        $this->assertEquals(true, $this->authorizationDataProvider->getUserAttributes(null)['ROLE_ADMIN']);
    }

    public function testUserAttributes(): void
    {
        // NOTE: user identifier is not required
        $this->setUpUserSession(['foo', '__']);

        $this->assertEquals(true, $this->authorizationDataProvider->getUserAttributes('username')['ROLE_USER']);
        $this->assertEquals(false, $this->authorizationDataProvider->getUserAttributes(null)['ROLE_ADMIN']);
        $this->assertEquals(false, $this->authorizationDataProvider->getUserAttributes(null)['ROLE_WRITER']);

        $this->setUpUserSession(['_', 'baz', '___']);

        $this->assertEquals(false, $this->authorizationDataProvider->getUserAttributes('username')['ROLE_USER']);
        $this->assertEquals(true, $this->authorizationDataProvider->getUserAttributes(null)['ROLE_ADMIN']);
        $this->assertEquals(true, $this->authorizationDataProvider->getUserAttributes(null)['ROLE_WRITER']);

        $this->setUpUserSession(['bar']);

        $this->assertEquals(true, $this->authorizationDataProvider->getUserAttributes('username')['ROLE_USER']);
        $this->assertEquals(false, $this->authorizationDataProvider->getUserAttributes(null)['ROLE_ADMIN']);
        $this->assertEquals(true, $this->authorizationDataProvider->getUserAttributes(null)['ROLE_WRITER']);

        $this->setUpUserSession(['baz', 'bar']);

        $this->assertEquals(true, $this->authorizationDataProvider->getUserAttributes('username')['ROLE_USER']);
        $this->assertEquals(true, $this->authorizationDataProvider->getUserAttributes(null)['ROLE_ADMIN']);
        $this->assertEquals(true, $this->authorizationDataProvider->getUserAttributes(null)['ROLE_WRITER']);
    }

    private function setUpUserSession(array $scopes): void
    {
        $this->authorizationDataProvider = new AuthorizationDataProvider(new DummyUserSessionProvider('username', $scopes));
        $this->authorizationDataProvider->setConfig(self::createAuthorizationConfig());
    }

    private static function createAuthorizationConfig(): array
    {
        return [
            'authorization_attributes' => [
                [
                    'name' => 'ROLE_USER',
                    'scope' => 'user',
                    'scopes' => ['foo', 'bar'],
                ],
                [
                    'name' => 'ROLE_ADMIN',
                    'scope' => 'admin',
                    'scopes' => ['baz'],
                ],
                [
                    'name' => 'ROLE_WRITER',
                    'scopes' => ['bar', 'baz'],
                ],
            ],
        ];
    }
}
