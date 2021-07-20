<?php

declare(strict_types=1);

namespace DBP\API\KeycloakBundle\DependencyInjection;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DbpKeycloakExtension extends ConfigurableExtension
{
    public function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        $certCacheDef = $container->register('dbp_api.cache.keycloak.keycloak_cert', FilesystemAdapter::class);
        $certCacheDef->setArguments(['core-keycloak-cert', 60, '%kernel.cache_dir%/dbp/keycloak-keycloak-cert']);
        $certCacheDef->addTag('cache.pool');

        $definition = $container->getDefinition('DBP\API\KeycloakBundle\Keycloak\KeycloakBearerUserProvider');
        $definition->addMethodCall('setConfig', [$mergedConfig['keycloak'] ?? []]);
        $definition->addMethodCall('setCertCache', [$certCacheDef]);
    }
}
