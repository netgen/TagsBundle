<?php

namespace Netgen\TagsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TagViewBuilderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('ezpublish.view_builder.registry')) {
            return;
        }

        $viewBuilderRegistry = $container->findDefinition('ezpublish.view_builder.registry');
        $tagViewBuilder = $container->findDefinition('eztags.view.tag_view_builder');

        $viewBuilderRegistry->addMethodCall(
            'addToRegistry',
            [[$tagViewBuilder]]
        );
    }
}
