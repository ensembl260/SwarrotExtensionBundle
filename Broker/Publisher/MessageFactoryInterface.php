<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Publisher;

use Swarrot\Broker\Message;

interface MessageFactoryInterface
{
    /**
     * @param mixed|null $body
     * @param array|mixed[] $properties
     *
     * @return Message
     */
    public function createMessage($body = null, array $properties = [], ?string $id = null): Message;
}
