<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Authenticator;

use Dbp\Relay\AuthBundle\OIDC\OIDError;
use Dbp\Relay\AuthBundle\OIDC\OIDProvider;
use Jose\Component\Core\JWKSet;
use Jose\Easy\Load;
use Jose\Easy\Validate;

class LocalTokenValidator extends TokenValidatorBase
{
    private $oidProvider;
    private $leewaySeconds;

    public function __construct(OIDProvider $oidProvider, int $leewaySeconds)
    {
        $this->oidProvider = $oidProvider;
        $this->leewaySeconds = $leewaySeconds;
    }

    /**
     * Validates the token locally using the public JWK of the OIDC server.
     *
     * This is faster because everything can be cached, but tokens/sessions revoked on the OIDC server
     * will still be considered valid as long as they are not expired.
     *
     * @return array the token
     *
     * @throws TokenValidationException
     */
    public function validate(string $accessToken): array
    {
        try {
            $jwks = $this->oidProvider->getJWKs();
            $providerConfig = $this->oidProvider->getProviderConfig();
        } catch (OIDError $e) {
            throw new TokenValidationException($e->getMessage());
        }

        $issuer = $providerConfig->getIssuer();
        // Allow the same algorithms that the introspection endpoint allows
        $algs = $providerConfig->getIntrospectionEndpointSigningAlgorithms();
        // The spec doesn't allow this, but just to be sure
        assert(!in_array('none', $algs, true));

        // Checks not needed/used here:
        // * sub(): This is the keycloak user ID by default, nothing we know beforehand
        // * jti(): Nothing we know beforehand
        // * aud(): The audience needs to be checked afterwards with checkAudience()
        try {
            $keySet = JWKSet::createFromKeyData($jwks);
            $validate = Load::jws($accessToken);
            $validate = $validate
                ->algs($algs)
                ->keyset($keySet)
                ->exp($this->leewaySeconds)
                ->iat($this->leewaySeconds)
                ->nbf($this->leewaySeconds)
                ->iss($issuer);
            assert($validate instanceof Validate);
            $jwtResult = $validate->run();
        } catch (\Exception $e) {
            throw new TokenValidationException('Token validation failed: '.$e->getMessage());
        }

        $jwt = $jwtResult->claims->all();

        // XXX: Keycloak will add extra data to the token returned by introspection, mirror this behaviour here
        // to avoid breakage when switching between local/remote validation.
        // https://github.com/keycloak/keycloak/blob/8225157a1cecef30034530aa/services/src/main/java/org/keycloak/protocol/oidc/AccessTokenIntrospectionProvider.java#L59
        if (isset($jwt['preferred_username'])) {
            $jwt['username'] = $jwt['preferred_username'];
        }
        if (!isset($jwt['username'])) {
            $jwt['username'] = null;
        }
        if (isset($jwt['azp'])) {
            $jwt['client_id'] = $jwt['azp'];
        }
        $jwt['active'] = true;

        return $jwt;
    }
}
