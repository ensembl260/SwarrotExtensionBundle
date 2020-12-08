<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Bab\RabbitMq\Configuration;
use Bab\RabbitMq\Action\RealAction;
use Bab\RabbitMq\HttpClient\CurlClient;
use Bab\RabbitMq\Logger\CliLogger;
use Bab\RabbitMq\VhostManager;

class VhostMappingCreateCommand extends Command
{
    private string $rabbitmqHost;
    private string $rabbitmqPort;
    private string $rabbitmqLogin;
    private string $rabbitmqPassword;

    public function __construct(string $rabbitmqHost, string $rabbitmqPort, string $rabbitmqLogin, string $rabbitmqPassword)
    {
        parent::__construct();

        $this->rabbitmqHost = $rabbitmqHost;
        $this->rabbitmqPort = $rabbitmqPort;
        $this->rabbitmqLogin = $rabbitmqLogin;
        $this->rabbitmqPassword = $rabbitmqPassword;
    }

    protected function configure(): void
    {
        $this->setName('swarrot_extension:vhost:mapping:create')
            ->setDescription('Create a vhost from a configuration file')
            ->addArgument('filepath', InputArgument::REQUIRED, 'Path to the configuration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configuration = new Configuration\Yaml($input->getArgument('filepath'));
        $vhost = $configuration->getVhost();
        $vhostManager = $this->getVhostManager($output, $vhost);
        $vhostManager->createMapping($configuration);

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param int|null|string $vhost
     *
     * @return VhostManager
     */
    private function getVhostManager(OutputInterface $output, $vhost): VhostManager
    {
        $credentials = [
            'vhost' => $vhost,
            'host' => $this->rabbitmqHost,
            'port' => (int) $this->rabbitmqPort,
            'user' => $this->rabbitmqLogin,
            'password' => $this->rabbitmqPassword,
        ];

        $httpClient = new CurlClient($credentials['host'], $credentials['port'], $credentials['user'], $credentials['password']);
        $action = new RealAction($httpClient);
        $logger = new CliLogger($output);
        $action->setLogger($logger);
        $vhostManager = new VhostManager($credentials, $action, $httpClient);
        $vhostManager->setLogger($logger);

        return $vhostManager;
    }
}
