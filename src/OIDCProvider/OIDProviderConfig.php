<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\OIDCProvider;

/**
 * discover: https://openid.net/specs/openid-connect-discovery-1_0.html
 * introspection: https://datatracker.ietf.org/doc/html/rfc8414.
 */
class OIDProviderConfig
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @throws OIDError
     */
    public static function fromString(string $data): OIDProviderConfig
    {
        try {
            $config = json_decode(
                $data, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new OIDError('Invalid config: '.$e->getMessage());
        }

        return new OIDProviderConfig($config);
    }

    public function getIssuer(): string
    {
        return $this->config['issuer'];
    }

    public function getJwksUri(): string
    {
        return $this->config['jwks_uri'];
    }

    public function getTokenEndpoint(): ?string
    {
        return $this->config['token_endpoint'] ?? null;
    }

    public function getIntrospectionEndpoint(): ?string
    {
        return $this->config['introspection_endpoint'] ?? null;
    }

    public function getIntrospectionEndpointSigningAlgorithms(): array
    {
        return $this->config['introspection_endpoint_auth_signing_alg_values_supported'] ?? [];
    }
}
