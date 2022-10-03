<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle;

use Dbp\Relay\AuthBundle\API\AuthorizationDataProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DbpRelayAuthBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        // add tag to all services implementing the interface
        $container->registerForAutoconfiguration(AuthorizationDataProviderInterface::class)
            ->addTag('auth.authorization_data_provider');
    }
}
