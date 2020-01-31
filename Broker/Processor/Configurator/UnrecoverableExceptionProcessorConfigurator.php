<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Processor\Configurator;

use MR\SwarrotExtensionBundle\Broker\Publisher\ErrorPublisher;
use Psr\Log\LoggerInterface;
use Swarrot\SwarrotBundle\Processor\ProcessorConfiguratorEnableAware;
use Swarrot\SwarrotBundle\Processor\ProcessorConfiguratorExtrasAware;
use Swarrot\SwarrotBundle\Processor\ProcessorConfiguratorInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @internal
 */
final class UnrecoverableExceptionProcessorConfigurator implements ProcessorConfiguratorInterface
{
    use ProcessorConfiguratorEnableAware, ProcessorConfiguratorExtrasAware;

    /**
     * @var ErrorPublisher
     */
    private $errorPublisher;

    /**
     * @var string
     */
    private $processorClass;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        string $processorClass,
        ErrorPublisher $errorPublisher,
        LoggerInterface $logger
    ) {
        $this->processorClass = $processorClass;
        $this->errorPublisher = $errorPublisher;
        $this->logger = $logger;
    }

    public function getProcessorArguments(array $options): array
    {
        return [
            $this->processorClass,
            $this->errorPublisher,
            $this->logger,
        ];
    }

    public function getCommandOptions(): array
    {
        return [];
    }

    public function resolveOptions(InputInterface $input): array
    {
        return $this->getExtras();
    }
}
