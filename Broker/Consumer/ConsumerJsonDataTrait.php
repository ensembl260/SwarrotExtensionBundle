<?php

namespace Ensembl260\SwarrotExtensionBundle\Broker\Consumer;

use Ensembl260\SwarrotExtensionBundle\Broker\Exception\InvalidDataException;
use Swarrot\Broker\Message;

trait ConsumerJsonDataTrait
{
    /**
     * @param array|mixed[] $options
     *
     * @return mixed
     *
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function getData(Message $message, array $options)
    {
        try {
            $data = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new InvalidDataException(sprintf('JSON error: "%s". Valid json expected.', $exception->getMessage()));
        }

        return $data;
    }
}
