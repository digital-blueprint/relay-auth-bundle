<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Authenticator;

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

    public function __construct(UserSessionInterface $userSession, OIDProvider $oidProvider)
    {
        $this->userSession = $userSession;
        $this->config = [];
        $this->oidProvider = $oidProvider;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function loadUserByToken(string $accessToken): UserInterface
    {
        $config = $this->config;
        if (!$config['remote_validation']) {
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
            throw new AuthenticationException('Invalid token');
        }

        if (($config['required_audience'] ?? '') !== '') {
            try {
                $validator::checkAudience($jwt, $config['required_audience']);
            } catch (TokenValidationException $e) {
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

        return new BearerUser(
            $identifier,
            $userRoles
        );
    }
}
