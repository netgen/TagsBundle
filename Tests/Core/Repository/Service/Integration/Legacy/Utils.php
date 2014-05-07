<?php

namespace Netgen\TagsBundle\Tests\Core\Repository\Service\Integration\Legacy;

use Netgen\TagsBundle\Core\Repository\TagsService;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\ExceptionConversion;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use RuntimeException;
use Exception;

/**
 * Utils class for setting up repository
 */
abstract class Utils
{
    /**
     * @var \eZ\Publish\Core\Base\ServiceContainer
     */
    protected static $serviceContainer;

    /**
     * @return \Netgen\TagsBundle\API\Repository\TagsService
     */
    public static function getTagsService()
    {
        /** @var \eZ\Publish\API\Repository\Repository $repository */
        $repository = self::$serviceContainer->get( "inner_repository" );

        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $legacyDbHandler */
        $legacyDbHandler = self::$serviceContainer->get( "legacy_db_handler" );

        $tagsHandler = new Handler(
            new ExceptionConversion(
                new DoctrineDatabase(
                    $legacyDbHandler
                )
            ),
            new Mapper()
        );

        return new TagsService( $repository, $tagsHandler );
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository
     */
    public static function getRepository()
    {
        // Override to set legacy handlers
        self::$serviceContainer = self::getServiceContainer(
            "persistence_handler_legacy",
            ( $dsn = getenv( "DATABASE" ) ) ? $dsn : "sqlite://:memory:"
        );

        // And inject data
        self::insertLegacyData( self::$serviceContainer->get( "legacy_db_handler" ) );

        // Return repository
        return self::$serviceContainer->get( "inner_repository" );
    }

    /**
     * @param string $persistenceHandler
     * @param string $ioHandler
     * @param string $dsn
     *
     * @throws \RuntimeException
     *
     * @return \eZ\Publish\Core\Base\ServiceContainer
     */
    protected static function getServiceContainer(
        $persistenceHandler = "persistence_handler_inmemory",
        $ioHandler = "io_handler_inmemory",
        $dsn = "sqlite://:memory:"
    )
    {
        // Get configuration config
        if ( !( $settings = include ( "vendor/ezsystems/ezpublish-kernel/config.php" ) ) )
        {
            throw new RuntimeException( "Could not find config.php, please copy config.php-DEVELOPMENT to config.php customize to your needs!" );
        }

        $settings["base"]["Configuration"]["UseCache"] = false;
        $settings["service"]["persistence_handler"]["alias"] = $persistenceHandler;
        $settings["service"]["io_handler"]["alias"] = $ioHandler;
        $settings["service"]["parameters"]["legacy_dsn"] = $dsn;

        /** START: Look for storage dir in eZ Publish 5 root */

        $settings["service"]["parameters"]["storage_dir"] = "var";

        /** END: Look for storage dir in eZ Publish 5 root */

        // Return Service Container
        return require "vendor/ezsystems/ezpublish-kernel/container.php";

    }

    /**
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $handler
     *
     * @throws \Exception
     */
    protected static function insertLegacyData( DatabaseHandler $handler )
    {
        $dsn = getenv( "DATABASE" );
        if ( !$dsn )
            $dsn = "sqlite://:memory:";
        $db = preg_replace( "(^([a-z]+).*)", "\\1", $dsn );

        // Insert Schema
        $tagsSchema = __DIR__ . "/../../../../../_fixtures/schema/schema." . $db . ".sql";
        $legacySchema = "vendor/ezsystems/ezpublish-kernel/eZ/Publish/Core/Persistence/Legacy/Tests/_fixtures/schema." . $db . ".sql";
        $queries = array_merge(
            array_filter( preg_split( "(;\\s*$)m", file_get_contents( $tagsSchema ) ) ),
            array_filter( preg_split( "(;\\s*$)m", file_get_contents( $legacySchema ) ) )
        );
        foreach ( $queries as $query )
        {
            $handler->exec( $query );
        }

        // Insert some default data
        $tagsData = require __DIR__ . "/../../../../../_fixtures/tags_tree.php";
        $legacyData = require "vendor/ezsystems/ezpublish-kernel/eZ/Publish/Core/Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezflow_dump.php";
        $data = array_merge( $legacyData, $tagsData );
        foreach ( $data as $table => $rows )
        {
            // Check that at least one row exists
            if ( !isset( $rows[0] ) )
            {
                continue;
            }

            $q = $handler->createInsertQuery();
            $q->insertInto( $handler->quoteIdentifier( $table ) );

            // Contains the bound parameters
            $values = array();

            // Binding the parameters
            foreach ( $rows[0] as $col => $val )
            {
                $q->set(
                    $handler->quoteIdentifier( $col ),
                    $q->bindParam( $values[$col] )
                );
            }

            $stmt = $q->prepare();

            foreach ( $rows as $row )
            {
                try
                {
                    // This CANNOT be replaced by:
                    // $values = $row
                    // each $values[$col] is a PHP reference which should be
                    // kept for parameters binding to work
                    foreach ( $row as $col => $val )
                    {
                        $values[$col] = $val;
                    }

                    $stmt->execute();
                }
                catch ( Exception $e )
                {
                    echo "$table ( ", implode( ", ", $row ), " )\n";
                    throw $e;
                }
            }
        }

        if ( $db === "pgsql" )
        {
            // Update PostgreSQL sequences
            $queries = array_merge(
                array_filter( preg_split( "(;\\s*$)m", file_get_contents( __DIR__ . "/../../../../../_fixtures/schema/setval.pgsql.sql" ) ) ),
                array_filter( preg_split( "(;\\s*$)m", file_get_contents( "vendor/ezsystems/ezpublish-kernel/eZ/Publish/Core/Persistence/Legacy/Tests/_fixtures/setval.pgsql.sql" ) ) )
            );
            foreach ( $queries as $query )
            {
                $handler->exec( $query );
            }
        }
    }
}
