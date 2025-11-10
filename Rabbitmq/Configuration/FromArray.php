<?php

namespace Ensembl260\SwarrotExtensionBundle\Rabbitmq\Configuration;

use Ensembl260\SwarrotExtensionBundle\Rabbitmq\Configuration;

class FromArray implements Configuration
{
    /** @var array */
    private $config;
    /** @var string */
    private $vhost;
    /** @var bool */
    private $hasDeadLetterExchange;
    /** @var bool */
    private $hasUnroutableExchange;

    public function __construct(array $configuration)
    {
        $this->vhost = key($configuration);
        $this->config = current($configuration);

        $parameters = $this['parameters'];

        $this->hasDeadLetterExchange = false;
        $this->hasUnroutableExchange = false;
        if (isset($parameters['with_dl'])) {
            $this->hasDeadLetterExchange = (bool) $parameters['with_dl'];
        }
        if (isset($parameters['with_unroutable'])) {
            $this->hasUnroutableExchange = (bool) $parameters['with_unroutable'];
        }
    }

    public function getVhost(): string
    {
        return $this->vhost;
    }

    public function hasDeadLetterExchange(): bool
    {
        return $this->hasDeadLetterExchange;
    }

    public function hasUnroutableExchange(): bool
    {
        return $this->hasUnroutableExchange;
    }

    public function offsetExists($offset): bool
    {
        return \array_key_exists($offset, $this->config);
    }

    public function offsetGet($offset)
    {
        return isset($this->config[$offset]) ? $this->config[$offset] : null;
    }

    public function offsetSet($offset, $value): void
    {
        throw new \LogicException('You shall not update configuration');
    }

    public function offsetUnset($offset): void
    {
        throw new \LogicException('No need to unset');
    }
}
