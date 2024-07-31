<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Authenticator;

use Dbp\Relay\AuthBundle\API\UserRolesInterface;
use Dbp\Relay\AuthBundle\Helpers\Tools;
use Dbp\Relay\AuthBundle\OIDCProvider\OIDProvider;
use Dbp\Relay\AuthBundle\TokenValidator\LocalTokenValidator;
use Dbp\Relay\AuthBundle\TokenValidator\RemoteTokenValidator;
use Dbp\Relay\AuthBundle\TokenValidator\TokenValidationException;
use Dbp\Relay\AuthBundle\UserSession\OIDCUserSessionProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class BearerUserProvider implements BearerUserProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $config;
    private $userSession;
    private $oidProvider;
    /**
     * @var UserRolesInterface
     */
    private $userRoles;
    /**
     * @var CacheInterface
     */
    private $cachePool;

    public function __construct(OIDCUserSessionProviderInterface $userSession, OIDProvider $oidProvider, UserRolesInterface $userRoles)
    {
        $this->userSession = $userSession;
        $this->config = [];
        $this->oidProvider = $oidProvider;
        $this->userRoles = $userRoles;
        $this->cachePool = new ArrayAdapter();
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function getValidationLeewaySeconds(): int
    {
        $config = $this->config;

        return $config['local_validation_leeway'];
    }

    public function usesRemoteValidation(): bool
    {
        return $this->config['remote_validation'];
    }

    public function setCache(CacheInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    public function loadUserByToken(string $accessToken): UserInterface
    {
        $config = $this->config;
        if (!$this->usesRemoteValidation()) {
            $leeway = $config['local_validation_leeway'];
            $validator = new LocalTokenValidator($this->oidProvider, $leeway);
        } else {
            $validator = new RemoteTokenValidator($this->oidProvider);
        }
        if ($this->logger !== null) {
            $validator->setLogger($this->logger);
        }

        try {
            $jwt = $validator->validate($accessToken);
        } catch (TokenValidationException $e) {
            $this->logger->info('Invalid token:', ['exception' => $e]);
            throw new AuthenticationException('Invalid token');
        }

        if (($config['required_audience'] ?? '') !== '') {
            try {
                $validator::checkAudience($jwt, $config['required_audience']);
            } catch (TokenValidationException $e) {
                $this->logger->info('Invalid audience:', ['exception' => $e]);
                throw new AuthenticationException('Invalid token audience');
            }
        }

        return $this->loadUserByValidatedToken($jwt);
    }

    /**
     * @return string[]
     */
    private function getUserRoles(?string $userIdentifier, array $scopes): array
    {
        $cacheKey = Tools::escapeCacheKey(json_encode([$this->userSession->getSessionCacheKey(), $userIdentifier, $scopes], JSON_THROW_ON_ERROR));

        return $this->cachePool->get($cacheKey, function (ItemInterface $item) use ($scopes, $userIdentifier): array {
            $item->expiresAfter($this->userSession->getSessionTTL());

            return $this->userRoles->getRoles($userIdentifier, $scopes);
        });
    }

    public function loadUserByValidatedToken(array $jwt): UserInterface
    {
        $session = $this->userSession;
        $session->setSessionToken($jwt);
        $scopes = Tools::extractScopes($jwt);
        $identifier = $session->getUserIdentifier();
        $userRoles = $this->getUserRoles($identifier, $scopes);

        return new BearerUser(
            $identifier,
            $userRoles
        );
    }
}
