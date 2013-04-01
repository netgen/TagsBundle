<?php

namespace EzSystems\TagsBundle\Tests\API\Repository\SetupFactory;

use eZ\Publish\API\Repository\Tests\SetupFactory\Legacy as BaseLegacy;
use eZ\Publish\API\Repository\Tests\SetupFactory;
use eZ\Publish\API\Repository\Tests\IdManager;

use eZ\Publish\Core\Base\ServiceContainer;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
class Legacy extends BaseLegacy
{
    /**
     * Initial data for eztags field type
     *
     * @var array
     */
    protected static $tagsInitialData;

    /**
     * Returns statements to be executed after data insert
     *
     * @return string[]
     */
    protected function getPostInsertStatements()
    {
        $statements = parent::getPostInsertStatements();

        if ( self::$db === "pgsql" )
        {
            $setvalPath = __DIR__ . "/../../../_fixtures/schema/setval.pgsql.sql";
            return array_merge( $statements, array_filter( preg_split( "(;\\s*$)m", file_get_contents( $setvalPath ) ) ) );
        }

        return $statements;
    }

    /**
     * Returns the initial database data
     *
     * @return array
     */
    protected function getInitialData()
    {
        parent::getInitialData();

        if ( !isset( self::$tagsInitialData ) )
        {
            self::$tagsInitialData = include __DIR__ . "/../../../_fixtures/tags_tree.php";
            self::$initialData = array_merge( self::$initialData, self::$tagsInitialData );
        }

        return self::$initialData;
    }

    /**
     * Returns the database schema as an array of SQL statements
     *
     * @return string[]
     */
    protected function getSchemaStatements()
    {
        $originalSchemaStatements = parent::getSchemaStatements();

        $tagsSchemaPath = __DIR__ . "/../../../_fixtures/schema/schema." . self::$db . ".sql";

        return array_merge( $originalSchemaStatements, array_filter( preg_split( "(;\\s*$)m", file_get_contents( $tagsSchemaPath ) ) ) );
    }

    /**
     * Returns the global ezpublish-kernel settings
     *
     * @return mixed
     */
    protected function getGlobalSettings()
    {
        if ( self::$globalSettings === null )
        {
            $settingsPath = __DIR__ . "/../../../../../../../vendor/ezsystems/ezpublish-kernel/config.php";

            if ( !file_exists( $settingsPath ) )
            {
                throw new \RuntimeException( "Could not find config.php, please copy config.php-DEVELOPMENT to config.php customize to your needs!" );
            }

            self::$globalSettings = include $settingsPath;
        }

        return self::$globalSettings;
    }

    /**
     * Returns the service container used for initialization of the repository
     *
     * @return \eZ\Publish\Core\Base\ServiceContainer
     */
    protected function getServiceContainer()
    {
        if ( !isset( self::$serviceContainer ) )
        {
            $configManager = $this->getConfigurationManager();

            $serviceSettings = $configManager->getConfiguration( "service" )->getAll();

            $serviceSettings["persistence_handler"]["alias"] = "persistence_handler_legacy";
            $serviceSettings["io_handler"]["alias"] = "io_handler_legacy";

            /** START: eztags field type settings */

            $serviceSettings["legacy_converter_registry"]["arguments"]["map"]["eztags"] = "EzSystems\\TagsBundle\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Tags::create";

            $serviceSettings["eztags:field_storage_legacy_gateway"]["class"] = "EzSystems\\TagsBundle\\Core\\FieldType\\Tags\\TagsStorage\\Gateway\\LegacyStorage";

            $serviceSettings["eztags:field_storage"]["class"] = "EzSystems\\TagsBundle\\Core\\FieldType\\Tags\\TagsStorage";
            $serviceSettings["eztags:field_storage"]["arguments"]["gateways"]["LegacyStorage"] = "@eztags:field_storage_legacy_gateway";

            $serviceSettings["eztags:field_type"]["class"] = "EzSystems\\TagsBundle\\Core\\FieldType\\Tags\\Type";

            /** END: eztags field type settings */

            /** START: Look for storage dir in eZ Publish 5 root */

            $serviceSettings["parameters"]["storage_dir"] = "var";

            /** END: Look for storage dir in eZ Publish 5 root */

            $serviceSettings["persistence_handler_legacy"]["arguments"]["config"]["dsn"] = self::$dsn;
            $serviceSettings["legacy_db_handler"]["arguments"]["dsn"] = self::$dsn;

            self::$serviceContainer = new ServiceContainer(
                $serviceSettings,
                $this->getDependencyConfiguration()
            );
        }

        return self::$serviceContainer;
    }
}
