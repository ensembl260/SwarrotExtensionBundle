<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class BrokerProcessorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('swarrot_extension.processor.abstract')) {
            return;
        }

        $brokerProcessors = $container->findTaggedServiceIds('broker.processor');

        foreach ($brokerProcessors as $id => $tags) {
            foreach ($tags as $tag) {
                $container
                    ->setDefinition($tag['id'], new ChildDefinition('swarrot_extension.processor.abstract'))
                    ->replaceArgument(0, new Reference($id))
                ;
            }
        }
    }
}
