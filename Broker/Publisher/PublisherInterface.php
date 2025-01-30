<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Broker\Publisher;

interface PublisherInterface
{
    /**
     * @param mixed[] $messageProperties
     * @param mixed[] $overridenConfig
     *
     * @throws \Ensembl260\SwarrotExtensionBundle\Broker\Exception\PublishException
     */
    public function publish(string $messageType, $data, array $messageProperties = [], array $overridenConfig = []): void;
}
