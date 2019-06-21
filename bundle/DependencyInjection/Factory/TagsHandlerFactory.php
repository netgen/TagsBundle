<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\DependencyInjection\Factory;

use Netgen\TagsBundle\SPI\Persistence\Tags\Handler;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class TagsHandlerFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Builds the tags handler.
     */
    public function buildTagsHandler(string $storageEngineIdentifier): Handler
    {
        return $this->container->get(
            sprintf(
                'eztags.api.storage_engine.%s.handler.tags',
                $storageEngineIdentifier
            )
        );
    }
}
