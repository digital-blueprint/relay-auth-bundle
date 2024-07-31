<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\TokenValidator;

use Dbp\Relay\AuthBundle\OIDCProvider\OIDError;
use Dbp\Relay\AuthBundle\OIDCProvider\OIDProvider;
use Jose\Component\Checker;
use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\Algorithm;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Symfony\Component\Clock\NativeClock;

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

        $keySet = JWKSet::createFromKeyData($jwks);

        $serializerManager = new JWSSerializerManager([
            new CompactSerializer(),
        ]);

        $algorithmManager = new AlgorithmManager([
            new Algorithm\RS256(),
            new Algorithm\RS384(),
            new Algorithm\RS512(),
            new Algorithm\PS256(),
            new Algorithm\PS384(),
            new Algorithm\PS512(),
        ]);

        $jwsVerifier = new JWSVerifier(
            $algorithmManager
        );

        $headerCheckerManager = new HeaderCheckerManager(
            [new AlgorithmChecker($algs, true)],
            [new JWSTokenSupport()],
        );

        $jwsLoader = new JWSLoader(
            $serializerManager,
            $jwsVerifier,
            $headerCheckerManager
        );

        $clock = new NativeClock();

        try {
            $jws = $jwsLoader->loadAndVerifyWithKeySet($accessToken, $keySet, $signature);
            $jwt = json_decode($jws->getPayload(), true, 512, JSON_THROW_ON_ERROR);

            // Checks not needed/used here:
            // * sub: This is the keycloak user ID by default, nothing we know beforehand
            // * jti: Nothing we know beforehand
            // * aud: The audience needs to be checked afterwards with checkAudience()
            $claimCheckerManager = new ClaimCheckerManager([
                new Checker\IssuedAtChecker($this->leewaySeconds, clock: $clock),
                new Checker\NotBeforeChecker($this->leewaySeconds, clock: $clock),
                new Checker\ExpirationTimeChecker($this->leewaySeconds, clock: $clock),
                new Checker\IssuerChecker([$issuer]),
            ]);
            $claimCheckerManager->check($jwt);
        } catch (\Exception $e) {
            throw new TokenValidationException('Token validation failed: '.$e->getMessage());
        }

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
