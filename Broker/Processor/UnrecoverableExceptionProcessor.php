<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Processor;

use MR\SwarrotExtensionBundle\Broker\Exception\UnrecoverableConsumerException;
use MR\SwarrotExtensionBundle\Broker\Exception\UnrecoverableException;
use MR\SwarrotExtensionBundle\Broker\Publisher\ErrorPublisher;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final class UnrecoverableExceptionProcessor implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected ProcessorInterface $processor;
    private ErrorPublisher $errorPublisher;

    public function __construct(ProcessorInterface $processor, ErrorPublisher $errorPublisher) {
        $this->processor = $processor;
        $this->errorPublisher = $errorPublisher;
        $this->setLogger(new NullLogger());
    }

    public function process(Message $message, array $options): bool
    {
        try {
            return $this->processor->process($message, $options);
        } catch (UnrecoverableException $unrecoverableException) {
            $this->logger->critical(
                '[UnrecoverableExceptionProcessor] An UnrecoverableException occurred.', 
                [
                    'exception' => $unrecoverableException, 
                    'message_id' => $message->getId(), 
                    'swarrot_processor' => 'unrecoverable_exception_processor'
                ]
            );
            $this->errorPublisher->exception($unrecoverableException);

            if ($unrecoverableException instanceof UnrecoverableConsumerException) {
                $wantRethrow = $unrecoverableException->wantRethrow();
                if ($wantRethrow) {
                    $this->logger->critical(
                        '[UnrecoverableExceptionProcessor] Gonna to rethrow the exception.', 
                        [
                            'exception' => $unrecoverableException, 
                            'message_id' => $message->getId(), 
                            'swarrot_processor' => 'unrecoverable_exception_processor'
                        ]
                    );
                    throw $unrecoverableException;
                }

                $wantKillConsumer = $unrecoverableException->wantKillConsumer();
                $this->logger->critical(
                    sprintf(
                        '[UnrecoverableExceptionProcessor] Gonna to %s consumer.', 
                        $wantKillConsumer ? 'kill' : 'let\'s run'
                    ), 
                    [
                        'killConsumer' => $wantKillConsumer, 
                        'exception' => $unrecoverableException, 
                        'message_id' => $message->getId(), 
                        'swarrot_processor' => 'unrecoverable_exception_processor'
                    ]
                );

                return !$wantKillConsumer;
            }
        }
    }
}
