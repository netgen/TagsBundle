<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\DependencyInjection\Factory;

use Symfony\Component\DependencyInjection\ContainerInterface;

final class TagsHandlerFactory
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function buildTagsHandler(string $storageEngineIdentifier): object
    {
        return $this->container->get(
            sprintf(
                'eztags.api.storage_engine.%s.handler.tags',
                $storageEngineIdentifier
            )
        );
    }
}
