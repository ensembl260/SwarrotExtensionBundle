<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Broker\Publisher;

interface SerializerInterface
{
    /**
     * @param mixed[] $context
     */
    public function serialize($data, string $format, array $context = []): string;
}
