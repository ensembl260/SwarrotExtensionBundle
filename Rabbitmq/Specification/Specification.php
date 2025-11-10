<?php

namespace Ensembl260\SwarrotExtensionBundle\Rabbitmq\Specification;

use Ensembl260\SwarrotExtensionBundle\Rabbitmq\Configuration;

interface Specification
{
    public function isSatisfiedBy(Configuration $config): bool;
}
