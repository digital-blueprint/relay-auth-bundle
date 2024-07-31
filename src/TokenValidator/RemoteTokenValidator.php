<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\TokenValidator;

use Dbp\Relay\AuthBundle\OIDCProvider\OIDError;
use Dbp\Relay\AuthBundle\OIDCProvider\OIDProvider;

class RemoteTokenValidator extends TokenValidatorBase
{
    private $oidProvider;

    public function __construct(OIDProvider $oidProvider)
    {
        $this->oidProvider = $oidProvider;
    }

    /**
     * Validates the token with the Keycloak introspection endpoint.
     *
     * @return array the token
     *
     * @throws TokenValidationException
     */
    public function validate(string $accessToken): array
    {
        try {
            $jwt = $this->oidProvider->introspectToken($accessToken);
        } catch (OIDError $e) {
            throw new TokenValidationException('Introspection failed: '.$e->getMessage());
        }

        if (!$jwt['active']) {
            throw new TokenValidationException('The token does not exist or is not valid anymore');
        }

        return $jwt;
    }
}
