<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Processor\Configurator;

use MR\SwarrotExtensionBundle\Broker\Publisher\ErrorPublisher;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Swarrot\SwarrotBundle\Processor\ProcessorConfiguratorEnableAware;
use Swarrot\SwarrotBundle\Processor\ProcessorConfiguratorExtrasAware;
use Swarrot\SwarrotBundle\Processor\ProcessorConfiguratorInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @internal
 */
final class UnrecoverableExceptionProcessorConfigurator implements ProcessorConfiguratorInterface, LoggerAwareInterface
{
    use ProcessorConfiguratorEnableAware, ProcessorConfiguratorExtrasAware, LoggerAwareTrait;

    private ErrorPublisher $errorPublisher;
    private string $processorClass;

    public function __construct(string $processorClass, ErrorPublisher $errorPublisher)
    {
        $this->processorClass = $processorClass;
        $this->errorPublisher = $errorPublisher;
        $this->setLogger(new NullLogger());
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
