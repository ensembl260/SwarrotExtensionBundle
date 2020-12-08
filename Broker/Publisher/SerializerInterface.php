<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Publisher;

interface SerializerInterface
{
    public function serialize(array $data, string $format, array $context = []): string;
}
