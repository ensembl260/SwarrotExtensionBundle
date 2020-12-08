<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Processor\Configurator;

use MR\SwarrotExtensionBundle\Broker\Processor\Callback\XDeathMaxLifetimeExceptionHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Swarrot\SwarrotBundle\Processor\ProcessorConfiguratorEnableAware;
use Swarrot\SwarrotBundle\Processor\ProcessorConfiguratorExtrasAware;
use Swarrot\SwarrotBundle\Processor\ProcessorConfiguratorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @internal
 */
final class XDeathMaxLifetimeProcessorConfigurator implements ProcessorConfiguratorInterface, LoggerAwareInterface
{
    use ProcessorConfiguratorEnableAware, ProcessorConfiguratorExtrasAware, LoggerAwareTrait;

    private string $processorClass;
    private XDeathMaxLifetimeExceptionHandler $exceptionHandler;

    public function __construct(string $processorClass, XDeathMaxLifetimeExceptionHandler $exceptionHandler)
    {
        $this->processorClass = $processorClass;
        $this->exceptionHandler = $exceptionHandler;
        $this->setLogger(new NullLogger());
    }

    public function getProcessorArguments(array $options): array
    {
        return [
            $this->processorClass,
            $options['queue'],
            $this->exceptionHandler,
            $this->logger,
        ];
    }

    public function getCommandOptions(): array
    {
        return [
            [
                'x-death-max-lifetime',
                'dl',
                InputOption::VALUE_REQUIRED,
                'Max x-death lifetime',
                $this->getExtra('x_death_max_lifetime', 3600),
            ],
        ];
    }

    public function resolveOptions(InputInterface $input): array
    {
        return ['x_death_max_lifetime' => (int) $input->getOption('x-death-max-lifetime')] + $this->getExtras();
    }
}
