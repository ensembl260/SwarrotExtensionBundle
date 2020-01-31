<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Publisher;

use MR\SwarrotExtensionBundle\Broker\Exception\PublishException;

interface PublisherInterface
{
    /**
     * @param string $messageType
     * @param mixed $data
     * @param array $messageProperties
     * @param array $overridenConfig
     *
     * @throws PublishException
     */
    public function publish(string $messageType, $data, array $messageProperties = [], array $overridenConfig = []): void;
}
