<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle;

use MR\SwarrotExtensionBundle\DependencyInjection\CompilerPass\BrokerProcessorPass;
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
