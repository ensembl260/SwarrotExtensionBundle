<?php

namespace Ensembl260\SwarrotExtensionBundle\Rabbitmq;

interface HttpClient
{
    public function query(string $verb, string $uri, ?array $parameters = null): string;
}
