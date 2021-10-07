<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('dbp_relay_auth');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('server_url')
                    ->info('The Keycloak server URL')
                    ->example('https://keycloak.example.com/auth')
                ->end()
                ->scalarNode('realm')
                    ->info('The Keycloak Realm')
                    ->example('myrealm')
                ->end()
                // API docs
                ->scalarNode('frontend_client_id')
                    ->info('The ID for the keycloak client (authorization code flow) used for API docs or similar')
                    ->example('client-docs')
                ->end()
                // Remote validation
                ->booleanNode('remote_validation')
                    ->info("If remote validation should be used. If set to false the token signature will\nbe only checked locally and not send to the keycloak server")
                    ->example(false)
                    ->defaultFalse()
                ->end()
                ->scalarNode('remote_validation_client_id')
                    ->info("The ID of the client (client credentials flow) used for remote token validation\n(optional)")
                    ->example('client-token-check')
                ->end()
                ->scalarNode('remote_validation_client_secret')
                    ->info('The client secret for the client referenced by client_id (optional)')
                    ->example('mysecret')
                ->end()
                // Settings for token validation
                ->scalarNode('required_audience')
                    ->info('If set only tokens which contain this audience are accepted (optional)')
                    ->example('my-api')
                ->end()
                ->integerNode('local_validation_leeway')
                    ->defaultValue(120)
                    ->min(0)
                    ->info("How much the system time of the API server and the Keycloak server\ncan be out of sync (in seconds). Used for local token validation.")
                ->end()
            ->end();

        return $treeBuilder;
    }
}
