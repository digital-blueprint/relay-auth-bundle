<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Service;

use Dbp\Relay\AuthBundle\Authenticator\OIDCUserSessionProviderInterface;
use Dbp\Relay\AuthBundle\DependencyInjection\Configuration;
use Dbp\Relay\CoreBundle\Authorization\AuthorizationDataProviderInterface;

class AuthorizationDataProvider implements AuthorizationDataProviderInterface
{
    /** @var string[][] */
    private array $attributeToScopeMap = [];

    private OIDCUserSessionProviderInterface $userSessionProvider;

    public function __construct(OIDCUserSessionProviderInterface $userSessionProvider)
    {
        $this->userSessionProvider = $userSessionProvider;
    }

    public function setConfig(array $config): void
    {
        $this->loadAttributeToScopeMapFromConfig($config[Configuration::ATTRIBUTES_ATTRIBUTE]);
    }

    public function getAvailableAttributes(): array
    {
        return array_keys($this->attributeToScopeMap);
    }

    public function getUserAttributes(?string $userIdentifier): array
    {
        $userScopes = $this->userSessionProvider->getScopes();
        $userAttributes = [];
        foreach ($this->attributeToScopeMap as $attribute => $scopes) {
            $userAttribute = false;
            foreach ($scopes as $scope) {
                if (in_array($scope, $userScopes, true)) {
                    $userAttribute = true;
                    break;
                }
            }
            $userAttributes[$attribute] = $userAttribute;
        }

        return $userAttributes;
    }

    private function loadAttributeToScopeMapFromConfig(array $attributes): void
    {
        foreach ($attributes as $attribute) {
            $scopes = $attribute[Configuration::SCOPES_ATTRIBUTE] ?? [];
            if (($scopeDeprecated = $attribute[Configuration::SCOPE_ATTRIBUTE] ?? null) !== null) {
                $scopes[] = $scopeDeprecated;
            }
            $this->attributeToScopeMap[$attribute[Configuration::NAME_ATTRIBUTE]] = $scopes;
        }
    }
}
