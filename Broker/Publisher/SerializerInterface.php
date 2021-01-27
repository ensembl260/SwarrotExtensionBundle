<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Publisher;

interface SerializerInterface
{
    /**
     * @param mixed $data
     * @param mixed[]  $context
     */
    public function serialize($data, string $format, array $context = []): string;
}
