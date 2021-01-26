<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Publisher;

interface PublisherInterface
{
    /**
     * @param mixed $data
     * @param mixed[] $messageProperties
     * @param mixed[] $overridenConfig
     *
     * @throws \MR\SwarrotExtensionBundle\Broker\Exception\PublishException
     */
    public function publish(string $messageType, $data, array $messageProperties = [], array $overridenConfig = []): void;
}
