<?php

namespace Netgen\TagsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TagViewBuilderPass implements CompilerPassInterface
{
    /**
     * Registers the tag view builder into view builder registry.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ezpublish.view_builder.registry')) {
            return;
        }

        $viewBuilderRegistry = $container->findDefinition('ezpublish.view_builder.registry');
        $tagViewBuilder = $container->findDefinition('eztags.view.tag_view_builder');

        $viewBuilderRegistry->addMethodCall(
            'addToRegistry',
            array(array($tagViewBuilder))
        );
    }
}
