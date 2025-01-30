<?php

namespace Ensembl260\SwarrotExtensionBundle\Broker\Processor\Configurator;

use Ensembl260\SwarrotExtensionBundle\Broker\Processor\Callback\XDeathMaxCountExceptionHandler;
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
final class XDeathMaxCountProcessorConfigurator implements ProcessorConfiguratorInterface, LoggerAwareInterface
{
    use ProcessorConfiguratorEnableAware;
    use ProcessorConfiguratorExtrasAware;
    use LoggerAwareTrait;

    private string $processorClass;
    private XDeathMaxCountExceptionHandler $exceptionHandler;

    public function __construct(string $processorClass, XDeathMaxCountExceptionHandler $exceptionHandler)
    {
        $this->processorClass = $processorClass;
        $this->exceptionHandler = $exceptionHandler;
        $this->setLogger(new NullLogger());
    }

    /**
     * @param mixed[] $options
     */
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
                'x-death-max-count',
                'dc',
                InputOption::VALUE_REQUIRED,
                'Max x-death count',
                $this->getExtra('x_death_max_count', 300),
            ],
        ];
    }

    public function resolveOptions(InputInterface $input): array
    {
        return ['x_death_max_count' => (int) $input->getOption('x-death-max-count')] + $this->getExtras();
    }
}
