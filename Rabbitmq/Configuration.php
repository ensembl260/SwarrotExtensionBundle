<?php

namespace Ensembl260\SwarrotExtensionBundle\Rabbitmq;

interface Configuration extends \ArrayAccess
{
    public function getVhost(): string;

    public function hasDeadLetterExchange(): bool;

    public function hasUnroutableExchange(): bool;
}
