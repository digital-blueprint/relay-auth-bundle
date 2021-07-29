<?php

declare(strict_types=1);

namespace Dbp\Relay\KeycloakBundle\DependencyInjection;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DbpRelayKeycloakExtension extends ConfigurableExtension implements PrependExtensionInterface
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

        $definition = $container->getDefinition('Dbp\Relay\KeycloakBundle\Keycloak\KeycloakBearerUserProvider');
        $definition->addMethodCall('setConfig', [$mergedConfig]);
        $definition->addMethodCall('setCertCache', [$certCacheDef]);
    }

    public function prepend(ContainerBuilder $container)
    {
        $config = $container->getExtensionConfig($this->getAlias())[0];
        $this->extendArrayParameter($container, 'dbp_api.twig_globals', [
            'keycloak_server_url' => $config['server_url'] ?? '',
            'keycloak_realm' => $config['realm'] ?? '',
            'keycloak_frontend_client_id' => $config['frontend_client_id'] ?? '',
        ]);
    }

    private function extendArrayParameter(ContainerBuilder $container, string $parameter, array $values)
    {
        if (!$container->hasParameter($parameter)) {
            $container->setParameter($parameter, []);
        }
        $oldValues = $container->getParameter($parameter);
        $container->setParameter($parameter, array_merge($oldValues, $values));
    }
}
