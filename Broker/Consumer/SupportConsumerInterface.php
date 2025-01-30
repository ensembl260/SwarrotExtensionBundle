<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Broker\Consumer;

use Swarrot\Broker\Message;

interface SupportConsumerInterface
{
    /**
     * This method allow you to specify if you want to skip a message or not
     * If you return false the message gonna be skipped.
     *
     * @param array|mixed[] $options
     */
    public function supportData($data, Message $message, array $options): bool;
}
