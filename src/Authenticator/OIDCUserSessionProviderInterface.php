<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Authenticator;

use Dbp\Relay\CoreBundle\API\UserSessionProviderInterface;

interface OIDCUserSessionProviderInterface extends UserSessionProviderInterface
{
    public function setSessionToken(?array $jwt): void;
}
