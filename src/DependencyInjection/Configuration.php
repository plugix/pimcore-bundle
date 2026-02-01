<?php

declare(strict_types=1);

namespace Plugix\PimcoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('plugix');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('api_key')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('Your Plugix API key (starts with sk_live_ or sk_test_)')
                ->end()
                ->scalarNode('api_url')
                    ->defaultValue('https://api.plugix.ai')
                    ->info('Plugix API URL')
                ->end()
                ->scalarNode('platform')
                    ->defaultValue('pimcore')
                    ->info('Platform identifier')
                ->end()
                ->arrayNode('mcp')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Enable MCP (Model Context Protocol) connection')
                        ->end()
                        ->booleanNode('auto_connect')
                            ->defaultTrue()
                            ->info('Automatically connect MCP on startup')
                        ->end()
                        ->integerNode('reconnect_interval')
                            ->defaultValue(5000)
                            ->info('Reconnect interval in milliseconds')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('features')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('product_descriptions')
                            ->defaultTrue()
                            ->info('Enable AI product descriptions')
                        ->end()
                        ->booleanNode('translations')
                            ->defaultTrue()
                            ->info('Enable AI translations')
                        ->end()
                        ->booleanNode('seo_optimization')
                            ->defaultTrue()
                            ->info('Enable AI SEO optimization')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('languages')
                    ->scalarPrototype()->end()
                    ->defaultValue(['en', 'de', 'fr', 'es', 'it', 'ru'])
                    ->info('Supported languages for translations')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
