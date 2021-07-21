<?php

declare(strict_types=1);

namespace DBP\API\KeycloakBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('dbp_keycloak');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('server_url')->end()
                ->scalarNode('realm')->end()
                ->scalarNode('client_id')->end()
                ->scalarNode('client_secret')->end()
                ->scalarNode('audience')->end()
                ->booleanNode('local_validation')->defaultTrue()->end()
                ->scalarNode('frontend_client_id')->end()
            ->end();

        return $treeBuilder;
    }
}
