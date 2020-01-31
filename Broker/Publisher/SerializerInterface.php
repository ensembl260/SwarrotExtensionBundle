<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Publisher;

interface SerializerInterface
{
    /**
     * @param mixed $data
     * @param mixed $format
     * @param array $context
     *
     * @return mixed
     */
    public function serialize($data, $format, array $context = []);
}
