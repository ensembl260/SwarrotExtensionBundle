<?php

namespace MR\SwarrotExtensionBundle\Broker\Processor\Configurator;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swarrot\SwarrotBundle\Processor\ProcessorConfiguratorEnableAware;
use Swarrot\SwarrotBundle\Processor\ProcessorConfiguratorExtrasAware;
use Swarrot\SwarrotBundle\Processor\ProcessorConfiguratorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @internal
 */
final class XDeathMaxCountProcessorConfigurator implements ProcessorConfiguratorInterface
{
    use ProcessorConfiguratorEnableAware, ProcessorConfiguratorExtrasAware;

    /**
     * @var string
     */
    private $processorClass;

    /**
     * @var callable
     */
    private $exceptionHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        string $processorClass,
        callable $exceptionHandler,
        LoggerInterface $logger = null
    ) {
        $this->processorClass = $processorClass;
        $this->exceptionHandler = $exceptionHandler;
        $this->logger = $logger ?: new NullLogger();
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
