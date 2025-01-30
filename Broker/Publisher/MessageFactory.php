<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Broker\Publisher;

use Swarrot\Broker\Message;

class MessageFactory implements MessageFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createMessage($body = null, array $properties = [], ?string $id = null): Message
    {
        return new Message($body, $properties, $id);
    }
}
