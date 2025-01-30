<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Broker\Publisher;

use Swarrot\Broker\Message;

interface MessageFactoryInterface
{
    /**
     * @param mixed|null    $body
     * @param array|mixed[] $properties
     */
    public function createMessage($body = null, array $properties = [], ?string $id = null): Message;
}
