<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Authenticator;

use Dbp\Relay\AuthBundle\API\AuthorizationDataProviderInterface;
use Dbp\Relay\CoreBundle\API\UserInterface;

class BearerUser implements UserInterface
{
    /** @var string[] */
    private $rolesDeprecated;

    /** @var string|null */
    private $identifier;

    /** @var array */
    private $roles;

    /** @var array */
    private $attributes;

    /** @var iterable */
    private $authorizationDataProviders;

    public function __construct(?string $identifier, array $rolesDeprecated)
    {
        $this->rolesDeprecated = $rolesDeprecated;
        $this->identifier = $identifier;

        $this->roles = [];
        $this->attributes = [];
        $this->authorizationDataProviders = [];
    }

    public function getRoles(): array
    {
        return $this->rolesDeprecated;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier ?? '';
    }

    public function eraseCredentials()
    {
    }

    public function setAuthorizationDataProviders(iterable $authorizationDataProviders)
    {
        $this->authorizationDataProviders = $authorizationDataProviders;
    }

    public function hasRole(string $roleName): bool
    {
        if (array_key_exists($roleName, $this->roles) === false) {
            $this->loadRole($roleName);
        }

        return $this->roles[$roleName] ?? false;
    }

    /**
     * @return mixed|null
     */
    public function getAttribute(string $attributeName)
    {
        if (array_key_exists($attributeName, $this->attributes) === false) {
            $this->loadAttributes($attributeName);
        }

        return $this->attributes[$attributeName] ?? null;
    }

    private function loadRole(string $roleName)
    {
        foreach ($this->authorizationDataProviders as $authorizationDataProvider) {
            $availableRoles = $authorizationDataProvider->getAvailableRoles();
            if (in_array($roleName, $availableRoles, true)) {
                $this->loadUserDataFromAuthorizationProvider($authorizationDataProvider);
                break;
            }
        }
    }

    private function loadAttributes(string $attributeName)
    {
        foreach ($this->authorizationDataProviders as $authorizationDataProvider) {
            $availableAttributes = $authorizationDataProvider->getAvailableAttributes();
            if (in_array($attributeName, $availableAttributes, true)) {
                $this->loadUserDataFromAuthorizationProvider($authorizationDataProvider);
                break;
            }
        }
    }

    private function loadUserDataFromAuthorizationProvider(AuthorizationDataProviderInterface $authorizationDataProvider)
    {
        $userRoles = [];
        $userAttributes = [];
        $authorizationDataProvider->getUserData($this->identifier, $userRoles, $userAttributes);

        foreach ($authorizationDataProvider->getAvailableAttributes() as $availableAttribute) {
            $this->attributes[$availableAttribute] = $userAttributes[$availableAttribute] ?? null;
        }

        foreach ($authorizationDataProvider->getAvailableRoles() as $availableRole) {
            $this->roles[$availableRole] = in_array($availableRole, $userRoles, true);
        }
    }
}
