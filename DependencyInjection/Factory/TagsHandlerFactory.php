<?php

namespace Netgen\TagsBundle\DependencyInjection\Factory;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class TagsHandlerFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Builds the tags handler.
     *
     * @param string $storageEngineIdentifier
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Handler
     */
    public function buildTagsHandler($storageEngineIdentifier)
    {
        return $this->container->get(
            sprintf(
                "eztags.api.storage_engine.%s.handler.tags",
                $storageEngineIdentifier
            )
        );
    }
}
