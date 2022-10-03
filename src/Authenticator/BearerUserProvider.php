<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Authenticator;

use Dbp\Relay\AuthBundle\Authorization\AuthorizationDataProviderProvider;
use Dbp\Relay\AuthBundle\OIDC\OIDProvider;
use Dbp\Relay\CoreBundle\API\UserSessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

class BearerUserProvider implements BearerUserProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $config;
    private $userSession;
    private $oidProvider;
    private $authorizationDataProviders;

    public function __construct(UserSessionInterface $userSession, OIDProvider $oidProvider)
    {
        $this->userSession = $userSession;
        $this->config = [];
        $this->oidProvider = $oidProvider;
    }

    /**
     * @required
     */
    public function injectAuthorizationDataProvders(AuthorizationDataProviderProvider $provider): void
    {
        $this->authorizationDataProviders = $provider->getAuthorizationDataProviders();
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

    public function loadUserByValidatedToken(array $jwt): UserInterface
    {
        $session = $this->userSession;
        $session->setSessionToken($jwt);
        $identifier = $session->getUserIdentifier();
        $userRoles = $session->getUserRoles();

        $user = new BearerUser(
            $identifier,
            $userRoles
        );

        if ($this->authorizationDataProviders !== null) {
            $user->setAuthorizationDataProviders($this->authorizationDataProviders);
        }

        return $user;
    }
}
