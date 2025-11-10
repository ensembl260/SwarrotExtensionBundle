<?php

namespace Ensembl260\SwarrotExtensionBundle\Rabbitmq\Logger;

use Psr\Log\AbstractLogger;
use Symfony\Component\Console\Output\OutputInterface;

class CliLogger extends AbstractLogger
{
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function log($level, $message, array $context = []): void
    {
        $this->output->writeln($message);
    }
}
