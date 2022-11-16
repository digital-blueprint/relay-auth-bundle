<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Service;

use Dbp\Relay\AuthBundle\DependencyInjection\Configuration;
use Dbp\Relay\CoreBundle\Authorization\AuthorizationDataProviderInterface;

class AuthorizationDataProvider implements AuthorizationDataProviderInterface
{
    /** @var string[] */
    private $attributeToScopeMap;

    /** @var OIDCUserSessionProvider */
    private $userSessionProvider;

    public function __construct(OIDCUserSessionProvider $userSessionProvider)
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

    public function getUserAttributes(string $userIdentifier): array
    {
        $userScopes = $this->userSessionProvider->getScopes();
        $userAttributes = [];
        foreach ($this->attributeToScopeMap as $attribute => $scope) {
            $userAttributes[$attribute] = in_array($scope, $userScopes, true);
        }

        return $userAttributes;
    }

    private function loadAttributeToScopeMapFromConfig(array $attributes)
    {
        foreach ($attributes as $attribute) {
            $this->attributeToScopeMap[$attribute[Configuration::NAME_ATTRIBUTE]] = $attribute[Configuration::SCOPE_ATTRIBUTE];
        }
    }
}
