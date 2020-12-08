<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Consumer;

use Swarrot\Broker\Message;

interface SupportConsumerInterface
{
    /**
     * This method allow you to specify if you want to skip a message or not
     * If you return false the message gonna be skipped
     *
     * @param mixed[] $data
     * @param Message $message
     * @param array $options
     *
     * @return bool
     */
    public function supportData(array $data, Message $message, array $options): bool;
}
