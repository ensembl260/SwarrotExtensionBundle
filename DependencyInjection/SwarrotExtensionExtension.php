<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SwarrotExtensionExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $container->setAlias('swarrot_extension.message_factory', $config['message_factory']);
        $container->setAlias('swarrot_extension.error.publisher', $config['error_publisher']['service']);
        $container->setParameter('swarrot_extension.error_publisher.routing_key_pattern', $config['error_publisher']['routing_key_pattern']);

        $url = $container->resolveEnvPlaceholders($config['admin_connection']['url'], true);
        $host = $port = $login = $password = null;

        if ($url) {
            $parsedUrl = parse_url($config['admin_connection']['url']);

            $host = $parsedUrl['host'] ?? null;
            $port = $parsedUrl['port'] ?? null;
            $login = $parsedUrl['user'] ?? null;
            $password = $parsedUrl['password'] ?? null;
        }

        $container->setParameter(
            'swarrot_extension.admin_connection.host',
            $config['admin_connection']['host'] ? $container->resolveEnvPlaceholders($config['admin_connection']['host'], true) : $host,
        );
        $container->setParameter(
            'swarrot_extension.admin_connection.port',
            $config['admin_connection']['port'] ? $container->resolveEnvPlaceholders($config['admin_connection']['port'], true) : $port,
        );
        $container->setParameter(
            'swarrot_extension.admin_connection.login',
            $config['admin_connection']['login'] ? $container->resolveEnvPlaceholders($config['admin_connection']['login'], true) : $login,
        );
        $container->setParameter(
            'swarrot_extension.admin_connection.password',
            $config['admin_connection']['password'] ? $container->resolveEnvPlaceholders($config['admin_connection']['password'], true) : $password,
        );

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    private function getDefaultConnectionConfiguration(ContainerBuilder $container): array
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (!isset($bundles['SwarrotBundle'])) {
            throw new \InvalidArgumentException('Swarrot bundle is required.');
        }

        $swarrotConfigs = $container->getExtensionConfig('swarrot');
        if (\count($swarrotConfigs) < 1) {
            throw new \InvalidArgumentException('Swarrot bundle configuration is not set. Please check swarrot bundle configuration.');
        }

        $firstSwarrotConfig = $swarrotConfigs[0];
        if (!isset($firstSwarrotConfig['default_connection'])) {
            throw new \InvalidArgumentException('A default connection is missing in swarrot bundle configuration.');
        }

        $defaultConnectionConfiguration = [];
        foreach ($firstSwarrotConfig['connections'] as $connectionName => $connectionConfiguration) {
            if ($connectionName === $firstSwarrotConfig['default_connection']) {
                $defaultConnectionConfiguration = $connectionConfiguration;
            }
        }

        if (!$defaultConnectionConfiguration) {
            throw new \InvalidArgumentException('Cannot found swarrot configuration for default connection. Please check swarrot bundle configuration.');
        }

        return $defaultConnectionConfiguration;
    }
}
