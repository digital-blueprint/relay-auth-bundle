<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Service;

use Dbp\Relay\AuthBundle\Authenticator\OIDCUserSessionProviderInterface;
use Dbp\Relay\AuthBundle\DependencyInjection\Configuration;
use Dbp\Relay\CoreBundle\Authorization\AuthorizationDataProviderInterface;

class AuthorizationDataProvider implements AuthorizationDataProviderInterface
{
    /** @var array[] */
    private $attributeToScopeMap;

    /** @var OIDCUserSessionProviderInterface */
    private $userSessionProvider;

    public function __construct(OIDCUserSessionProviderInterface $userSessionProvider)
    {
        $this->attributeToScopeMap = [];
        $this->userSessionProvider = $userSessionProvider;
    }

    public function setConfig(array $config)
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

    private function loadAttributeToScopeMapFromConfig(array $attributes)
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
