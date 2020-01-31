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
    public function getData(Message $message, array $options)
    {
        $data = json_decode($message->getBody(), true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidDataException(sprintf('JSON error: "%s". Valid json expected.', json_last_error_msg()));
        }

        return $data;
    }
}
