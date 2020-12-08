<?php

namespace MR\SwarrotExtensionBundle\Broker\Processor;

use MR\SwarrotExtensionBundle\Broker\Consumer\ConstraintConsumerInterface;
use MR\SwarrotExtensionBundle\Broker\Consumer\ConsumerInterface;
use MR\SwarrotExtensionBundle\Broker\Consumer\SupportConsumerInterface;
use MR\SwarrotExtensionBundle\Broker\Exception\InvalidDataException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * This class is use to wrap your domain consumer
 * This class is call by swarrot consumer with $message and $options
 * and do many calls on injected consumer like this
 *
 *      $data = $consumer->getData($message, $options); Allow you to extract the data from the Message
 *      $consumer->supportData($data, $message, $options); Allow you to skip a message
 *      $consumer->getConstraints($data, $message, $options); Allow you to Validate the data
 *      $consumer->consumeData($data, $message, $options); You must do your domain process here
 *
 * all those actions are try catch in order to log and trace what's going during message process
 */
class Processor implements ProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ConsumerInterface $consumer;
    private string $consumerClass;
    private ValidatorInterface $validator;

    public function __construct(
        ConsumerInterface $consumer,
        ValidatorInterface $validator
    ) {
        $this->consumer = $consumer;
        $this->consumerClass = get_class($this->consumer);
        $this->validator = $validator;
        $this->logger = new NullLogger();
    }

    /**
     * get/decode -> support -> validate -> consume
     *
     * @param Message $message
     * @param mixed[] $options
     *
     * @return void|bool
     *
     * @throws \Exception
     */
    public function process(Message $message, array $options): bool
    {
        $this->logger->info('Start consuming message #{message_id}.', ['message_id' => $message->getId(), 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor']);

        try {
            $data = $this->consumer->getData($message, $options);
        } catch (InvalidDataException $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception, 'message_id' => $message->getId(), 'message_properties' => $message->getProperties(), 'message_body' => $message->getBody(), 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor']);

            return true;
        }

        if ($this->consumer instanceof SupportConsumerInterface) {
            try {
                if (!$this->consumer->supportData($data, $message, $options)) {
                    $this->logger->info('Consumer not support message.', ['message_id' => $message->getId(), 'message_properties' => $message->getProperties(), 'message_body' => $message->getBody(), 'data' => $data, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor']);

                    return true;
                }
                $this->logger->info('Consumer support message.', ['message_id' => $message->getId(), 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor']);
            } catch (\Exception $exception) {
                $this->logger->error('Exception during consumer supportData.', ['exception' => $exception, 'message_id' => $message->getId(), 'message_properties' => $message->getProperties(), 'message_body' => $message->getBody(), 'data' => $data, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor']);

                throw $exception;
            }
        }

        if ($this->consumer instanceof ConstraintConsumerInterface) {
            try {
                if (null !== $constraints = $this->consumer->getConstraints($data, $message, $options)) {
                    $violations = $this->validator->validate($data, $constraints);

                    // refacto this
                    if (0 < count($violations)) {
                        $this->logger->warning('Invalid data for consumer.', ['violations' => $violations, 'message_id' => $message->getId(), 'data' => $data, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor']);

                        return true;
                    }
                    $this->logger->info('Valid data for consumer.', ['message_id' => $message->getId(), 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor']);
                }
            } catch (UnexpectedTypeException $exception) {
                $this->logger->error('UnexpectedTypeException during data validation.', ['exception' => $exception, 'message_id' => $message->getId(), 'data' => $data, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor']);

                return true;
            } catch (\Exception $exception) {
                $this->logger->error('Exception during data validation.', ['exception' => $exception, 'message_id' => $message->getId(), 'data' => $data, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor']);

                throw $exception;
            }
        }

        try {
            $return = $this->consumer->consumeData($data, $message, $options);

            $this->logger->info('Message consumed.', ['message_id' => $message->getId(), 'message_properties' => $message->getProperties(), 'message_body' => $message->getBody(), 'data' => $data, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor']);

            return $return;
        } catch (\Exception $exception) {
            $this->logger->error('Exception during consume data.', ['exception' => $exception, 'message_id' => $message->getId(), 'data' => $data, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor']);

            throw $exception;
        }
    }
}
