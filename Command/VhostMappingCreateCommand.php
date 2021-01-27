<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Command;

use Bab\RabbitMq\Action\RealAction;
use Bab\RabbitMq\Configuration;
use Bab\RabbitMq\HttpClient\CurlClient;
use Bab\RabbitMq\Logger\CliLogger;
use Bab\RabbitMq\VhostManager;
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
        array $retryQueuesConfig
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
            ->addOption('retry-queues', 'rq', InputOption::VALUE_OPTIONAL, 'Load default retry queues', true)
        ;
    }

    /**
     * {@inheritDoc}
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
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
