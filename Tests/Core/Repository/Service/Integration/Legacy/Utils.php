<?php

namespace Netgen\TagsBundle\Tests\Core\Repository\Service\Integration\Legacy;

/**
 * Utils class for setting up repository.
 */
abstract class Utils
{
    /**
     * @var \Netgen\TagsBundle\Tests\Core\Repository\Service\Integration\Legacy\SetupFactory
     */
    public static $setupFactory;

    /**
     * @return \Netgen\TagsBundle\API\Repository\TagsService
     */
    public static function getTagsService()
    {
        if (static::$setupFactory === null) {
            static::$setupFactory = static::getSetupFactory();
        }

        // Return tags service
        return static::$setupFactory->getTagsService();
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository
     */
    final public static function getRepository()
    {
        if (static::$setupFactory === null) {
            static::$setupFactory = static::getSetupFactory();
        }

        // Return repository
        return static::$setupFactory->getRepository();
    }

    /**
     * @return \Netgen\TagsBundle\Tests\Core\Repository\Service\Integration\Legacy\SetupFactory
     */
    protected static function getSetupFactory()
    {
        return new SetupFactory();
    }
}
