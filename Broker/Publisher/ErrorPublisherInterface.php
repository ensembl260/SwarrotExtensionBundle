<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Broker\Publisher;

use Ensembl260\SwarrotExtensionBundle\Broker\Processor\Event\XDeathEvent;

interface ErrorPublisherInterface
{
    /**
     * Publishes an error message for a given exception.
     *
     * @throws \Ensembl260\SwarrotExtensionBundle\Broker\Exception\PublishException if the publish failed
     */
    public function exception(\Throwable $exception): void;

    /**
     * Publishes an error message for a given XDeathEvent.
     *
     * @throws \Ensembl260\SwarrotExtensionBundle\Broker\Exception\PublishException if the publish failed
     */
    public function xdeathEvent(XDeathEvent $xDeathEvent): void;
}
