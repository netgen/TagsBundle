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
        if (!$container->hasParameter('ibexa.api.storage_engine.default')) {
            return;
        }

        /** @var string $defaultStorageEngine */
        $defaultStorageEngine = $container->getParameter('ibexa.api.storage_engine.default');

        $container->setAlias(
            'netgen_tags.api.persistence_handler.tags.storage',
            sprintf(
                'netgen_tags.api.storage_engine.%s.handler.tags',
                $defaultStorageEngine
            )
        );
    }
}
