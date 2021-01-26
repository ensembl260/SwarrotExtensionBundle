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
     * @param mixed[] $options
     *
     * @return void|bool
     *
     * @throws \Exception
     */
    public function process(Message $message, array $options): bool
    {
        $this->logger->info('Start consuming message #{message_id}.', $this->buildLoggerContext($message));

        try {
            $data = $this->consumer->getData($message, $options);
        } catch (InvalidDataException $exception) {
            $this->logger->error($exception->getMessage(), $this->buildLoggerContext($message, null, $exception, true, true));

            return true;
        }

        if ($this->consumer instanceof SupportConsumerInterface) {
            try {
                if (!$this->consumer->supportData($data, $message, $options)) {
                    $this->logger->info('Consumer not support message.', $this->buildLoggerContext($message, $data, null, true, true));

                    return true;
                }

                $this->logger->info('Consumer support message.', $this->buildLoggerContext($message));
            } catch (\Throwable $exception) {
                $this->logger->error('Exception during consumer supportData.', $this->buildLoggerContext($message, $data, $exception, true, true));

                throw $exception;
            }
        }

        if ($this->consumer instanceof ConstraintConsumerInterface) {
            try {
                $constraints = $this->consumer->getConstraints($data, $message, $options);

                if (null !== $constraints) {
                    $violations = $this->validator->validate($data, $constraints);

                    if (0 < $violations->count()) {
                        $this->logger->warning('Invalid data for consumer.', array_merge($this->buildLoggerContext($message, $data), ['violations' => $violations]));

                        return true;
                    }

                    $this->logger->info('Valid data for consumer.', $this->buildLoggerContext($message));
                }
            } catch (UnexpectedTypeException $exception) {
                $this->logger->error('UnexpectedTypeException during data validation.', $this->buildLoggerContext($message, $data, $exception));

                return true;
            } catch (\Throwable $exception) {
                $this->logger->error('Exception during data validation.', $this->buildLoggerContext($message, $data, $exception));

                throw $exception;
            }
        }

        try {
            $return = $this->consumer->consumeData($data, $message, $options);

            $this->logger->info('Message consumed.', $this->buildLoggerContext($message, $data, null, true, true));

            return $return;
        } catch (\Throwable $exception) {
            $this->logger->error('Exception during consume data.', $this->buildLoggerContext($message, $data, $exception));

            throw $exception;
        }
    }

    /**
     * @param mixed $data
     */
    private function buildLoggerContext(
        Message $message,
        $data = null,
        ?\Throwable $exception = null,
        bool $withMessageProperties = false,
        bool $withMessageBody = false
    ): array {
        $context = [
            'message_id' => $message->getId(),
            'consumer' => $this->consumerClass,
            'swarrot_processor' => 'consumer_processor',
        ];

        if ($data) {
            $context['data'] = $data;
        }

        if ($exception) {
            $context['exception'] = $exception;
        }

        if ($withMessageProperties) {
            $context['message_properties'] = $message->getProperties();
        }

        if ($withMessageBody) {
            $context['message_body'] = $message->getBody();
        }

        return $context;
    }
}
