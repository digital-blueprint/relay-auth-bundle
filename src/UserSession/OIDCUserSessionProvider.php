<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\UserSession;

use Dbp\Relay\AuthBundle\Helpers\Tools;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OIDCUserSessionProvider implements OIDCUserSessionProviderInterface
{
    /**
     * @var ?array
     */
    private $jwt;

    /**
     * @var ParameterBagInterface
     */
    private $parameters;

    public function __construct(ParameterBagInterface $parameters)
    {
        $this->jwt = null;
        $this->parameters = $parameters;
    }

    public function getUserIdentifier(): ?string
    {
        if ($this->jwt === null || self::isServiceAccountToken($this->jwt)) {
            return null;
        }

        return $this->jwt['username'] ?? null;
    }

    /**
     * Given a token returns if the token was generated through a client credential flow.
     */
    public static function isServiceAccountToken(array $jwt): bool
    {
        $scopes = Tools::extractScopes($jwt);

        // XXX: This is the main difference I found compared to other flows, but that's a Keycloak
        // implementation detail I guess.
        $has_openid_scope = in_array('openid', $scopes, true);

        return !$has_openid_scope;
    }

    public function setSessionToken(?array $jwt): void
    {
        $this->jwt = $jwt;
    }

    public function getScopes(): array
    {
        return Tools::extractScopes($this->jwt ?? []);
    }

    public function getSessionLoggingId(): string
    {
        $unknown = 'unknown';

        if ($this->jwt === null) {
            return $unknown;
        }
        assert($this->jwt !== null);

        // We want to know where the request is coming from and which requests likely belong together for debugging
        // purposes while still preserving the privacy of the user.
        // The session ID gets logged in the Keycloak event log under 'code_id' and stays the same during a login
        // session. When the event in keycloak expires it's no longer possible to map the ID to a user.
        // The keycloak client ID is in azp, so add that too, and hash it with the user ID so we get different
        // user ids for different clients for the same session.

        $jwt = $this->jwt;
        $client = $jwt['azp'] ?? $unknown;
        if (!isset($jwt['session_state'])) {
            $user = $unknown;
        } else {
            $appSecret = $this->parameters->has('kernel.secret') ? $this->parameters->get('kernel.secret') : '';
            $user = substr(hash('sha256', $client.$jwt['session_state'].$appSecret), 0, 6);
        }

        return $client.'-'.$user;
    }

    public function getSessionCacheKey(): string
    {
        assert($this->jwt !== null);

        return hash('sha256', $this->getUserIdentifier().'.'.json_encode($this->jwt));
    }

    public function getSessionTTL(): int
    {
        assert($this->jwt !== null);

        if (!isset($this->jwt['exp']) || !isset($this->jwt['iat'])) {
            return -1;
        }

        return max($this->jwt['exp'] - $this->jwt['iat'], 0);
    }
}
