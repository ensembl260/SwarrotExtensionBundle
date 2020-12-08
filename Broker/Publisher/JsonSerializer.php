<?php

namespace MR\SwarrotExtensionBundle\Broker\Publisher;

class JsonSerializer implements SerializerInterface
{
    public function serialize(array $data, string $format, array $context = []): string
    {
        if ('json' !== $format) {
            throw new \InvalidArgumentException('This serializer only support "json".');
        }

        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
