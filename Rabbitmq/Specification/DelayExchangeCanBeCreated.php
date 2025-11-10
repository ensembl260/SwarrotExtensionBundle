<?php

namespace Ensembl260\SwarrotExtensionBundle\Rabbitmq\Specification;

use Ensembl260\SwarrotExtensionBundle\Rabbitmq\Configuration;

class DelayExchangeCanBeCreated implements Specification
{
    public function isSatisfiedBy(Configuration $config): bool
    {
        if (!isset($config['queues']) || empty($config['queues'])) {
            return false;
        }

        foreach ($config['queues'] as $name => $parameters) {
            if (isset($parameters['delay'])) {
                return true;
            }
        }

        return false;
    }
}
