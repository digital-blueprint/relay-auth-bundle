<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\OIDC;

use Dbp\Relay\AuthBundle\Helpers\Tools;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\Psr6CacheStorage;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class OIDProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $config;
    private $cachePool;
    private $clientHandler;
    private $serverConfig;

    /* The duration the public keycloak config/cert is cached */
    private const CACHE_TTL_SECONDS = 3600;

    public function __construct()
    {
        $this->config = [];
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function setCache(?CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    /**
     * Replace the guzzle client handler for testing.
     *
     * @param object $handler
     */
    public function setClientHandler(?object $handler)
    {
        $this->clientHandler = $handler;
    }

    private function getClient(): Client
    {
        $stack = HandlerStack::create($this->clientHandler);
        if ($this->logger !== null) {
            $stack->push(Tools::createLoggerMiddleware($this->logger));
        }
        $options = [
            'handler' => $stack,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];

        $client = new Client($options);

        if ($this->cachePool !== null) {
            $cacheMiddleWare = new CacheMiddleware(
                new GreedyCacheStrategy(
                    new Psr6CacheStorage($this->cachePool),
                    self::CACHE_TTL_SECONDS
                )
            );
            $stack->push($cacheMiddleWare);
        }

        return $client;
    }

    /**
     * @throws OIDError
     */
    public function getProviderConfig(): OIDProviderConfig
    {
        if (!$this->serverConfig) {
            $serverUrl = $this->config['server_url'] ?? '';
            $configUrl = $serverUrl.'/.well-known/openid-configuration';
            $client = $this->getClient();
            try {
                $response = $client->request('GET', $configUrl);
            } catch (GuzzleException $e) {
                throw new OIDError('Config fetching failed: '.$e->getMessage());
            }
            $data = (string) $response->getBody();
            $this->serverConfig = OIDProviderConfig::fromString($data);
        }

        return $this->serverConfig;
    }

    /**
     * Fetches the JWKs from the OID server.
     *
     * @throws OIDError
     */
    public function getJWKs(): array
    {
        $providerConfig = $this->getProviderConfig();
        $certsUrl = $providerConfig->getJwksUri();
        $client = $this->getClient();

        try {
            $response = $client->request('GET', $certsUrl);
        } catch (GuzzleException $e) {
            throw new OIDError('Cert fetching failed: '.$e->getMessage());
        }

        try {
            $jwks = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new OIDError('Cert fetching, invalid json: '.$e->getMessage());
        }

        return $jwks;
    }

    /**
     * Introspect the token via the provider. Note that you have to check the result to see if the
     * token is valid/active.
     *
     * @throws OIDError
     */
    public function introspectToken(string $token): array
    {
        $providerConfig = $this->getProviderConfig();
        $introspectEndpoint = $providerConfig->getIntrospectionEndpoint();
        if ($introspectEndpoint === null) {
            throw new OIDError('No introspection endpoint');
        }

        $authId = $this->config['remote_validation_id'] ?? '';
        $authSecret = $this->config['remote_validation_secret'] ?? '';
        if ($authId === '' || $authSecret === '') {
            throw new OIDError('remote_validation_id/secret not set');
        }

        $client = $this->getClient();

        try {
            // keep in mind that even if we are doing this request with a different client id the data returned will be
            // from the client id of token $token (that's important for mapped attributes)
            $response = $client->request('POST', $introspectEndpoint, [
                'auth' => [$authId, $authSecret],
                'form_params' => [
                    'token' => $token,
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new OIDError('Token introspection failed');
        }

        $data = (string) $response->getBody();
        try {
            $jwt = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new OIDError('Token introspection failed, invalid json: '.$e->getMessage());
        }

        return $jwt;
    }
}
