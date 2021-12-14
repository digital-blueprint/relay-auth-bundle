<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dbp_relay_auth');
        $treeBuilder->getRootNode()
            ->children()
                // Note: "<server_url>/.well-known/openid-configuration" has to exist
                ->scalarNode('server_url')
                    ->info('The base URL for the OIDC server (in case of Keycloak fort the specific realm)')
                    ->example('https://keycloak.example.com/auth/realms/my-realm')
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

                // Remote validation
                ->booleanNode('remote_validation')
                    ->info("If remote validation should be used. If set to false the token signature will\nbe only checked locally and not send to the keycloak server")
                    ->example(false)
                    ->defaultFalse()
                ->end()
                ->scalarNode('remote_validation_id')
                    ->info("The ID of the client (client credentials flow) used for remote token validation\n(optional)")
                    ->example('client-token-check')
                ->end()
                ->scalarNode('remote_validation_secret')
                    ->info('The client secret for the client referenced by client_id (optional)')
                    ->example('mysecret')
                ->end()

                // API docs. This is still Keycloak specific because we only have a keycloak
                // web component right now.
                ->scalarNode('frontend_keycloak_server')
                    ->info('The Keycloak server base URL')
                    ->example('https://keycloak.example.com/auth')
                ->end()
                ->scalarNode('frontend_keycloak_realm')
                    ->info('The keycloak realm')
                    ->example('client-docs')
                ->end()
                ->scalarNode('frontend_keycloak_client_id')
                    ->info('The ID for the keycloak client (authorization code flow) used for API docs or similar')
                    ->example('client-docs')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
