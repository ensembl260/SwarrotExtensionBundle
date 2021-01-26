<?php

namespace MR\SwarrotExtensionBundle\Broker\Consumer;

use MR\SwarrotExtensionBundle\Broker\Exception\InvalidDataException;
use Swarrot\Broker\Message;

trait ConsumerJsonDataTrait
{
    /**
     * @return mixed
     */
    public function getData(Message $message)
    {
        try {
            $data = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new InvalidDataException(sprintf('JSON error: "%s". Valid json expected.', $exception->getMessage()));
        }

        return $data;
    }
}
