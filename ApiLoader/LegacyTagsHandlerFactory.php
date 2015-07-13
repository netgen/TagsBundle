<?php

namespace Netgen\TagsBundle\ApiLoader;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase;
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
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     *
     * @return \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler
     */
    public function buildLegacyTagsHandler( DatabaseHandler $dbHandler )
    {
        $languageHandler = $this->container->get( 'ezpublish.spi.persistence.legacy.language.handler' );
        $languageMaskGenerator = $this->container->get( 'ezpublish.persistence.legacy.language.mask_generator' );

        $legacyTagsHandlerClass = $this->container->getParameter( "ezpublish.api.storage_engine.legacy.handler.tags.class" );
        return new $legacyTagsHandlerClass(
            new ExceptionConversion(
                new DoctrineDatabase( $dbHandler, $languageHandler, $languageMaskGenerator )
            ),
            new Mapper( $languageHandler, $languageMaskGenerator )
        );
    }
}
