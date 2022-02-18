<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\DependencyInjection\Compiler;

use Ibexa\Core\MVC\Symfony\View\Builder\ViewBuilderRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class TagViewBuilderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(ViewBuilderRegistry::class)) {
            return;
        }

        $viewBuilderRegistry = $container->findDefinition(ViewBuilderRegistry::class);
        $tagViewBuilder = $container->findDefinition('netgen_tags.view.tag_view_builder');

        $viewBuilderRegistry->addMethodCall(
            'addToRegistry',
            [[$tagViewBuilder]]
        );
    }
}
