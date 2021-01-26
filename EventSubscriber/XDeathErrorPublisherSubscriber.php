<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\EventSubscriber;

use MR\SwarrotExtensionBundle\Broker\Processor\Event\XDeathEvent;
use MR\SwarrotExtensionBundle\Broker\Publisher\ErrorPublisher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
final class XDeathErrorPublisherSubscriber implements EventSubscriberInterface
{
    private ErrorPublisher $publisher;

    public function __construct(ErrorPublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            XDeathEvent::MAX_COUNT_REACHED => 'onXDeathReached',
            XDeathEvent::MAX_LIFETIME_REACHED => 'onXDeathReached',
        ];
    }

    public function onXDeathReached(XDeathEvent $event): void
    {
        $this->publisher->xdeathEvent($event);
    }
}
