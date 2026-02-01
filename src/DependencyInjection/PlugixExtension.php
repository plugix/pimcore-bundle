<?php

declare(strict_types=1);

namespace Plugix\PimcoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PlugixExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Set parameters
        $container->setParameter('plugix.api_key', $config['api_key']);
        $container->setParameter('plugix.api_url', $config['api_url']);
        $container->setParameter('plugix.platform', $config['platform']);
        $container->setParameter('plugix.mcp.enabled', $config['mcp']['enabled']);
        $container->setParameter('plugix.mcp.auto_connect', $config['mcp']['auto_connect']);
        $container->setParameter('plugix.mcp.reconnect_interval', $config['mcp']['reconnect_interval']);
        $container->setParameter('plugix.features', $config['features']);
        $container->setParameter('plugix.languages', $config['languages']);

        // Load services
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }

    public function getAlias(): string
    {
        return 'plugix';
    }
}
