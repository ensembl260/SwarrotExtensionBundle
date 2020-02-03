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
        $treeBuilder = new TreeBuilder('swarrot_extension', 'array');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('admin_connection')
                    ->children()
                        ->scalarNode('host')->defaultValue('127.0.0.1')->end()
                        ->scalarNode('port')->defaultValue(15672)->end()
                        ->scalarNode('login')->defaultValue('guest')->end()
                        ->scalarNode('password')->defaultValue('guest')->end()
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
