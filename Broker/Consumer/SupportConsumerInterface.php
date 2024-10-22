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
     * @param mixed $data
     * @param array|mixed[] $options
     *
     * @return bool
     */
    public function supportData($data, Message $message, array $options): bool;
}
