<?php

namespace Netgen\TagsBundle\ApiLoader;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\ExceptionConversion;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LegacyTagsHandlerFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Builds the legacy tags handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     *
     * @return \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler
     */
    public function buildLegacyTagsHandler(DatabaseHandler $dbHandler)
    {
        $languageHandler = $this->container->get('ezpublish.spi.persistence.legacy.language.handler');
        $languageMaskGenerator = $this->container->get('ezpublish.persistence.legacy.language.mask_generator');

        return new Handler(
            new ExceptionConversion(
                new DoctrineDatabase($dbHandler, $languageHandler, $languageMaskGenerator)
            ),
            new Mapper($languageHandler, $languageMaskGenerator)
        );
    }
}
