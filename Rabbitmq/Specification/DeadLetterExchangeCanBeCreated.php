<?php

namespace Ensembl260\SwarrotExtensionBundle\Rabbitmq\Specification;

use Ensembl260\SwarrotExtensionBundle\Rabbitmq\Configuration;

class DeadLetterExchangeCanBeCreated implements Specification
{
    public function isSatisfiedBy(Configuration $config): bool
    {
        if (true === $config->hasDeadLetterExchange()) {
            return true;
        }

        if (!isset($config['queues']) || empty($config['queues'])) {
            return false;
        }

        foreach ($config['queues'] as $name => $parameters) {
            if (isset($parameters['with_dl']) && true === (bool) $parameters['with_dl']) {
                return true;
            }

            if (isset($parameters['retries'])) {
                return true;
            }
        }

        return false;
    }
}
