<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Authorization;

class AuthorizationDataProviderProvider
{
    private $authorizationDataProviders;

    public function __construct(iterable $authorizationDataProviders)
    {
        $this->authorizationDataProviders = $authorizationDataProviders;
    }

    public function getAuthorizationDataProviders(): iterable
    {
        return $this->authorizationDataProviders;
    }
}
