<?php

namespace MR\SwarrotExtensionBundle\Broker\Consumer;

use MR\SwarrotExtensionBundle\Broker\Exception\InvalidDataException;
use Swarrot\Broker\Message;

trait ConsumerJsonDataTrait
{
    /**
     * @param Message $message
     * @param array $options
     *
     * @return mixed
     * @throws \Exception
     */
    public function getData(Message $message, array $options): array
    {
        try {
            return json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            throw new InvalidDataException(sprintf('JSON error: "%s". Valid json expected.', json_last_error_msg()));
        }
    }
}
