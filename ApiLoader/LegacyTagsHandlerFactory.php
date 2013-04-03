<?php

namespace Netgen\TagsBundle\ApiLoader;

use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\ExceptionConversion;

class LegacyTagsHandlerFactory
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
     * Builds the legacy tags handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     *
     * @return \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler
     */
    public function buildLegacyTagsHandler( EzcDbHandler $dbHandler )
    {
        $legacyTagsHandlerClass = $this->container->getParameter( "ezpublish.api.storage_engine.legacy.handler.tags.class" );
        return new $legacyTagsHandlerClass(
            new ExceptionConversion(
                new EzcDatabase( $dbHandler )
            ),
            new Mapper()
        );
    }
}
