<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle;

use Ensembl260\SwarrotExtensionBundle\DependencyInjection\CompilerPass\BrokerProcessorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SwarrotExtensionBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new BrokerProcessorPass());
    }
}
