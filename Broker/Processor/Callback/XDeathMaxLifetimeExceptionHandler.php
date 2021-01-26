<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Processor\Callback;

use MR\SwarrotExtensionBundle\Broker\Processor\Event\XDeathEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
final class XDeathMaxLifetimeExceptionHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->setLogger(new NullLogger());
    }

    /**
     * @param mixed[] $options
     */
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
                new XDeathEvent(XDeathEvent::MAX_LIFETIME_REACHED, $exception, $message, $options),
                XDeathEvent::MAX_LIFETIME_REACHED,
            );

        return true;
    }
}
