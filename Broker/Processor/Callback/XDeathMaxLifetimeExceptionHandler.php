<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Processor\Callback;

use MR\SwarrotExtensionBundle\Broker\Processor\Event\XDeathEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
final class XDeathMaxLifetimeExceptionHandler
{
    /**
     * @var EventDispatcherInterface|LegacyEventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($eventDispatcher, LoggerInterface $logger = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger ?: new NullLogger();
    }

    public function __invoke(\Throwable $exception, Message $message, array $options): bool
    {
        $this
            ->logger
            ->critical(
                '[XDeathProcessor] Max lifetime have been reached ({x_death_max_lifetime}s).',
                [
                    'x_death_max_lifetime' => $options['x_death_max_lifetime'],
                    'exception' => $exception,
                    'message_id' => $message->getId(),
                    'swarrot_processor' => 'x_death_lifetime_processor',
                ]
            );

        $this
            ->eventDispatcher
            ->dispatch(
                XDeathEvent::MAX_LIFETIME_REACHED,
                new XDeathEvent(XDeathEvent::MAX_LIFETIME_REACHED, $exception, $message, $options)
            );

        return true;
    }
}
