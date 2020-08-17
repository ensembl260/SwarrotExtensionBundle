<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('swarrot_extension', 'array');

        $rootNode
            ->children()
                ->arrayNode('admin_connection')
                    ->children()
                        ->scalarNode('host')->defaultValue(null)->end()
                        ->scalarNode('port')->defaultValue(null)->end()
                        ->scalarNode('login')->defaultValue(null)->end()
                        ->scalarNode('password')->defaultValue(null)->end()
                        ->scalarNode('url')->defaultValue(null)->end()
                    ->end()
                ->end()
                ->scalarNode('message_factory')->defaultValue('swarrot_extension.publisher.message_factory.default')->end()
                ->arrayNode('error_publisher')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('service')->defaultValue('swarrot_extension.error_publisher.default')->end()
                        ->scalarNode('routing_key_pattern')->defaultValue('%s')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
