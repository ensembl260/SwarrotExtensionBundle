<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Broker\Processor\Callback;

use Ensembl260\SwarrotExtensionBundle\Broker\Processor\Event\XDeathEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Swarrot\Broker\Message;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
final class XDeathMaxCountExceptionHandler implements LoggerAwareInterface
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
                '[XDeathProcessor] Max retry count have been reached ({x_death_max_count}).',
                [
                    'x_death_max_count' => $options['x_death_max_count'],
                    'exception' => $exception,
                    'message_id' => $message->getId(),
                    'swarrot_processor' => 'x_death_count_processor',
                ]
            );

        $this
            ->eventDispatcher
            ->dispatch(
                new XDeathEvent(XDeathEvent::MAX_COUNT_REACHED, $exception, $message, $options),
                XDeathEvent::MAX_COUNT_REACHED,
            );

        return true;
    }
}
