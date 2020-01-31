<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class BrokerProcessorPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('swarrot_extension.processor.abstract')) {
            return;
        }

        $brokerProcessors = $container->findTaggedServiceIds('broker.processor');
        /** DefinitionDecorator will be drop in v4.0 */
        $childDefinitionClass = class_exists('\Symfony\Component\DependencyInjection\ChildDefinition')
            ? ChildDefinition::class
            : DefinitionDecorator::class;

        foreach ($brokerProcessors as $id => $tags) {
            foreach ($tags as $tag) {
                $container
                    ->setDefinition($tag['id'], new $childDefinitionClass('swarrot_extension.processor.abstract'))
                    ->replaceArgument(0, new Reference($id));
            }
        }
    }
}
