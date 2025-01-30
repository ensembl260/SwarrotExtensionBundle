<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SwarrotExtensionExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $container->setAlias('swarrot_extension.message_factory', $config['message_factory']);
        $container->setAlias('swarrot_extension.error.publisher', $config['error_publisher']['service']);
        $container->setParameter('swarrot_extension.error_publisher.routing_key_pattern', $config['error_publisher']['routing_key_pattern']);

        $adminConfig = $this->buildAdminConnection($config, $container);

        $container->setParameter('swarrot_extension.admin_connection.host', $adminConfig['host']);
        $container->setParameter('swarrot_extension.admin_connection.port', $adminConfig['port']);
        $container->setParameter('swarrot_extension.admin_connection.login', $adminConfig['login']);
        $container->setParameter('swarrot_extension.admin_connection.password', $adminConfig['password']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param string[][] $config
     */
    private function buildAdminConnection(array $config, ContainerBuilder $container): array
    {
        $adminConnection = [
            'host' => '127.0.0.1',
            'port' => '15672',
            'login' => 'guest',
            'password' => 'guest',
        ];

        if (!$config['admin_connection']) {
            return $adminConnection;
        }

        $adminConnectionConfig = $config['admin_connection'];
        $parsedUrl = parse_url($container->resolveEnvPlaceholders($adminConnectionConfig['url'], true));

        $adminConnection['host'] = $this->buildAdminHost($parsedUrl, $adminConnectionConfig);
        $adminConnection['port'] = $this->buildAdminPort($parsedUrl, $adminConnectionConfig);
        $adminConnection['login'] = $this->buildAdminLogin($parsedUrl, $adminConnectionConfig);
        $adminConnection['password'] = $this->buildAdminPassword($parsedUrl, $adminConnectionConfig);

        return $adminConnection;
    }

    /**
     * @param string[] $parsedUrl
     * @param string[] $config
     */
    private function buildAdminHost(array $parsedUrl, array $config): string
    {
        $host = '127.0.0.1';

        $host = $parsedUrl['host'] ?? $host;
        $host = $config['host'] ?? $host;

        return $config['force_ssl'] ? 'https://'.$host : 'http://'.$host;
    }

    /**
     * @param string[] $parsedUrl
     * @param string[] $config
     */
    private function buildAdminPort(array $parsedUrl, array $config): string
    {
        $port = '15672';
        $port = $parsedUrl['port'] ?? $port;
        $port = $config['port'] ?? $port;

        return (string) $port;
    }

    /**
     * @param string[] $parsedUrl
     * @param string[] $config
     */
    private function buildAdminLogin(array $parsedUrl, array $config): string
    {
        $login = 'guest';
        $login = $parsedUrl['user'] ?? $login;
        $login = $config['login'] ?? $login;

        return $login;
    }

    /**
     * @param string[] $parsedUrl
     * @param string[] $config
     */
    private function buildAdminPassword(array $parsedUrl, array $config): string
    {
        $password = 'guest';
        $password = $parsedUrl['pass'] ?? $password;
        $password = $config['password'] ?? $password;

        return $password;
    }
}
