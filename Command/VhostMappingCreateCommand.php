<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Command;

use Ensembl260\SwarrotExtensionBundle\Rabbitmq\Action\RealAction;
use Ensembl260\SwarrotExtensionBundle\Rabbitmq\Configuration;
use Ensembl260\SwarrotExtensionBundle\Rabbitmq\HttpClient\CurlClient;
use Ensembl260\SwarrotExtensionBundle\Rabbitmq\Logger\CliLogger;
use Ensembl260\SwarrotExtensionBundle\Rabbitmq\VhostManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;

class VhostMappingCreateCommand extends Command
{
    private string $rabbitmqHost;
    private string $rabbitmqPort;
    private string $rabbitmqLogin;
    private string $rabbitmqPassword;

    /** @var array|mixed[] */
    private array $retryQueuesConfig;

    /**
     * @param array|mixed[] $retryQueuesConfig
     */
    public function __construct(
        string $rabbitmqHost,
        string $rabbitmqPort,
        string $rabbitmqLogin,
        string $rabbitmqPassword,
        array $retryQueuesConfig,
    ) {
        parent::__construct();

        $this->rabbitmqHost = $rabbitmqHost;
        $this->rabbitmqPort = $rabbitmqPort;
        $this->rabbitmqLogin = $rabbitmqLogin;
        $this->rabbitmqPassword = $rabbitmqPassword;
        $this->retryQueuesConfig = $retryQueuesConfig;
    }

    protected function configure(): void
    {
        $this->setName('swarrot_extension:vhost:mapping:create')
            ->setDescription('Create a vhost from a configuration file')
            ->addArgument('filepath', InputArgument::REQUIRED, 'Path to the configuration file')
            ->addOption('retry-queues', 'r', InputOption::VALUE_NONE, 'Load default retry queues')
        ;
    }

    /**
     * {@inheritDoc}
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('filepath');

        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException(sprintf('File "%s" doesn\'t exist', $filePath));
        }

        $yaml = new Parser();
        $configuration = $yaml->parse(file_get_contents($filePath));

        if ($input->getOption('retry-queues')) {
            $configuration[key($configuration)] = array_merge_recursive(current($configuration), $this->retryQueuesConfig);
        }

        $configuration = new Configuration\FromArray($configuration);
        $vhost = $configuration->getVhost();
        $vhostManager = $this->getVhostManager($output, $vhost);
        $vhostManager->createMapping($configuration);

        return 0;
    }

    /**
     * @param int|string|null $vhost
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
