<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Publisher;

use MR\SwarrotExtensionBundle\Broker\Exception\PublishException;
use MR\SwarrotExtensionBundle\Broker\Processor\Event\XDeathEvent;

interface ErrorPublisherInterface
{
    /**
     * Publishes an error message for a given exception.
     * @throws PublishException If the publish failed.
     */
    public function exception(\Throwable $exception): void;

    /**
     * Publishes an error message for a given XDeathEvent.
     *
     * @throws PublishException If the publish failed.
     */
    public function xdeathEvent(XDeathEvent $xDeathEvent): void;
}
