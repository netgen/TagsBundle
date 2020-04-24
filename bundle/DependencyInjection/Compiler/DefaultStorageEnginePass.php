<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use function sprintf;

final class DefaultStorageEnginePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('ezpublish.api.storage_engine.default')) {
            return;
        }

        $container->setAlias(
            'eztags.api.persistence_handler.tags.storage',
            sprintf(
                'eztags.api.storage_engine.%s.handler.tags',
                $container->getParameter('ezpublish.api.storage_engine.default')
            )
        );
    }
}
