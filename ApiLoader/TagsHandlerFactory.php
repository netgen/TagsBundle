<?php

namespace EzSystems\TagsBundle\ApiLoader;

use Symfony\Component\DependencyInjection\ContainerInterface;

class TagsHandlerFactory
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * Constructor
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct( ContainerInterface $container )
    {
        $this->container = $container;
    }

    /**
     * Builds the tags handler
     *
     * @param string $storageEngineIdentifier
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Handler
     */
    public function buildTagsHandler( $storageEngineIdentifier )
    {
        return $this->container->get( "ezpublish.api.storage_engine.$storageEngineIdentifier.handler.tags" );
    }
}
