<?php

namespace MR\SwarrotExtensionBundle\Broker\Publisher;

class JsonSerializer implements SerializerInterface
{
    /**
     * @param mixed $data
     * @param mixed $format
     * @param array $context
     *
     * @return string
     */
    public function serialize($data, $format, array $context = [])
    {
        if ('json' !== $format) {
            throw new \InvalidArgumentException('This serializer only support "json".');
        }

        return json_encode($data);
    }
}
