<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Consumer;

use Swarrot\Broker\Message;

/**
 * Your consumer must implement this interface
 * and don't forget to tag your consumer with
 *
 * <tag name="broker.processor" id="your_processor_service_name" />
 *
 * and use "your_processor_service_name" in swarrot consumer.processor key
 */
interface ConsumerInterface
{
    /**
     * This method allow you to extract the data from the message
     * this data was send to consumeData after
     *
     * /!\ If you encounter a parse error during the get Data you can throw an
     * MR\SwarrotExtensionBundle\Broker\Exception\InvalidDataException
     * in order to log and specify that you can never consume this message
     *
     * @param Message $message
     * @param array $options
     *
     * @return mixed
     */
    public function getData(Message $message, array $options);

    /**
     * Implement your domain process in this method
     * you have access to $data (come from your getData above) and swarrot $message and $options
     * if you throw an exception it gonna to be log and rethrow
     * (ACKProcessor will nack'ed message)
     *
     * Returning false will stop the consumer
     * Otherwise, returning void will let the consumer alive
     *
     * Most of the time you will have to return void
     *
     * @param mixed $data
     * @param Message $message
     * @param array $options
     *
     * @return void|false
     */
    public function consumeData($data, Message $message, array $options);
}
