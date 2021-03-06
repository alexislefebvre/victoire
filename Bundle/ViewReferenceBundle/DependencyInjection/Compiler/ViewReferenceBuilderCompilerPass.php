<?php

namespace Victoire\Bundle\ViewReferenceBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ViewReferenceBuilderCompilerPass.
 */
class ViewReferenceBuilderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('victoire_view_reference.builder_chain')) {
            return;
        }
        $definition = $container->getDefinition(
            'victoire_view_reference.builder_chain'
        );
        $taggedServices = $container->findTaggedServiceIds(
            'victoire_view_reference.view_reference.builder'
        );
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (!array_key_exists('view', $attributes)) {
                    throw new InvalidConfigurationException('View class attribute is not defined for '.$id);
                }
                $definition->addMethodCall(
                    'addViewReferenceBuilder',
                    [new Reference($id), $attributes['view']]
                );
            }
        }
    }
}
