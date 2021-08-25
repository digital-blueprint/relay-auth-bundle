<?php

declare(strict_types=1);

namespace Dbp\Relay\KeycloakBundle\Keycloak;

use Dbp\Relay\CoreBundle\API\UserSessionInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class KeycloakBearerUserProvider implements KeycloakBearerUserProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $config;
    private $certCachePool;
    private $personCachePool;
    private $userSession;

    public function __construct(UserSessionInterface $userSession)
    {
        $this->userSession = $userSession;
        $this->config = [];
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function setCertCache(?CacheItemPoolInterface $cachePool)
    {
        $this->certCachePool = $cachePool;
    }

    public function loadUserByToken(string $accessToken): UserInterface
    {
        $config = $this->config;
        $keycloak = new Keycloak(
            $config['server_url'], $config['realm'],
            $config['remote_validation_client_id'], $config['remote_validation_client_secret']);

        if (!$config['remote_validation']) {
            $leeway = $config['local_validation_leeway'];
            $validator = new KeycloakLocalTokenValidator($keycloak, $this->certCachePool, $leeway);
        } else {
            $validator = new KeycloakRemoteTokenValidator($keycloak);
        }
        $validator->setLogger($this->logger);

        try {
            $jwt = $validator->validate($accessToken);
        } catch (TokenValidationException $e) {
            throw new AccessDeniedException('Invalid token');
        }

        if (($config['required_audience'] ?? '') !== '') {
            try {
                $validator::checkAudience($jwt, $config['required_audience']);
            } catch (TokenValidationException $e) {
                throw new AccessDeniedException('Invalid token audience');
            }
        }

        return $this->loadUserByValidatedToken($jwt);
    }

    public function loadUserByValidatedToken(array $jwt): UserInterface
    {
        $session = $this->userSession;
        $session->setSessionToken($jwt);
        $identifier = $session->getUserIdentifier();
        $userRoles = $session->getUserRoles();

        return new KeycloakBearerUser(
            $identifier,
            $userRoles
        );
    }
}
