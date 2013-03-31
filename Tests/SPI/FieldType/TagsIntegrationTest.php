<?php

namespace EzSystems\TagsBundle\Tests\SPI\FieldType;

use eZ\Publish\SPI\Tests\FieldType\BaseIntegrationTest;
use EzSystems\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags as TagsConverter;
use EzSystems\TagsBundle\Core\FieldType\Tags\Type as TagsType;
use EzSystems\TagsBundle\Core\FieldType\Tags\TagsStorage;
use EzSystems\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway\LegacyStorage as TagsLegacyStorage;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Field;

/**
 * Integration test for legacy storage field types
 *
 * This abstract base test case is supposed to be the base for field type
 * integration tests. It basically calls all involved methods in the field type
 * ``Converter`` and ``Storage`` implementations. Fo get it working implement
 * the abstract methods in a sensible way.
 *
 * The following actions are performed by this test using the custom field
 * type:
 *
 * - Create a new content type with the given field type
 * - Load create content type
 * - Create content object of new content type
 * - Load created content
 * - Copy created content
 * - Remove copied content
 *
 * @group integration
 */
class TagsIntegrationTest extends BaseIntegrationTest
{
    /**
     * Only set up once for these read only tests on a large fixture
     *
     * Skipping the reset-up, since setting up for these tests takes quite some
     * time, which is not required to spent, since we are only reading from the
     * database anyways.
     */
    public function setUp()
    {
        if ( !self::$setUp )
        {
            parent::setUp();

            $handler = $this->getDatabaseHandler();

            $schema = __DIR__ . "/../../Core/Persistence/Legacy/_fixtures/schema." . $this->db . ".sql";

            $queries = array_filter( preg_split( "(;\\s*$)m", file_get_contents( $schema ) ) );
            foreach ( $queries as $query )
            {
                $handler->exec( $query );
            }

            $this->insertDatabaseFixture( __DIR__ . "/../../Core/Repository/Service/Integration/Legacy/_fixtures/clean_eztags_tables.php" );
        }
    }

    /**
     * Reset DB sequences
     */
    public function resetSequences()
    {
        parent::resetSequences();

        switch ( $this->db )
        {
            case "pgsql":
            {
                // Update PostgreSQL sequences
                $handler = $this->getDatabaseHandler();

                $queries = array_filter( preg_split( "(;\\s*$)m", file_get_contents( __DIR__ . "/../../Core/Persistence/Legacy/_fixtures/setval.pgsql.sql" ) ) );
                foreach ( $queries as $query )
                {
                    $handler->exec( $query );
                }
            }
        }
    }

    /**
     * Returns the identifier of the FieldType under test
     *
     * @return string
     */
    public function getTypeName()
    {
        return "eztags";
    }

    /**
     * Returns the Handler with all necessary objects registered
     *
     * Returns an instance of the Persistence Handler where the
     * FieldType\Storage has been registered.
     *
     * @return \eZ\Publish\SPI\Persistence\Handler
     */
    public function getCustomHandler()
    {
        $handler = $this->getHandler();

        $handler->getFieldTypeRegistry()->register(
            "eztags",
            new TagsType()
        );
        $handler->getStorageRegistry()->register(
            "eztags",
            new TagsStorage(
                array(
                    "LegacyStorage" => new TagsLegacyStorage(),
                )
            )
        );
        $handler->getFieldValueConverterRegistry()->register(
            "eztags",
            new TagsConverter()
        );

        return $handler;
    }

    /**
     * Returns the FieldTypeConstraints to be used to create a field definition
     * of the FieldType under test.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints
     */
    public function getTypeConstraints()
    {
        return new FieldTypeConstraints();
    }

    /**
     * Returns the field definition data expected after loading the newly
     * created field definition with the FieldType under test
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getFieldDefinitionData()
    {
        return array(
            // The eztags field type does not have any special field definition properties
            array( "fieldType", "eztags" ),
            array( "fieldTypeConstraints", new FieldTypeConstraints() ),
        );
    }

    /**
     * Get initial field value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getInitialValue()
    {
        return new FieldValue(
            array(
                "data" => null,
                "externalData" => array(
                    array(
                        "id" => 42,
                        "parent_id" => 21,
                        "main_tag_id" => 0,
                        "keyword" => "Croatia",
                        "depth" => 3,
                        "path_string" => "/1/21/42/",
                        "modified" => 1234567,
                        "remote_id" => "123456abcdef"
                    )
                ),
                "sortKey" => null,
            )
        );
    }

    /**
     * Asserts that the loaded field data is correct
     *
     * Performs assertions on the loaded field, mainly checking that the
     * $field->value->externalData is loaded correctly. If the loading of
     * external data manipulates other aspects of $field, their correctness
     * also needs to be asserted. Make sure you implement this method agnostic
     * to the used SPI\Persistence implementation!
     */
    public function assertLoadedFieldDataCorrect( Field $field )
    {
        $this->assertEquals(
            $this->getInitialValue()->externalData,
            $field->value->externalData
        );

        $this->assertNull( $field->value->data );
        $this->assertNull( $field->value->sortKey );
    }

    /**
     * Get update field value.
     *
     * Use to update the field
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getUpdatedValue()
    {
        return new FieldValue(
            array(
                "data" => null,
                "externalData" => array(
                    array(
                        "id" => 24,
                        "parent_id" => 12,
                        "main_tag_id" => 42,
                        "keyword" => "Hrvatska",
                        "depth" => 3,
                        "path_string" => "/1/12/24/",
                        "modified" => 7654321,
                        "remote_id" => "abcdef123456"
                    )
                ),
                "sortKey" => null,
            )
        );
    }

    /**
     * Asserts that the updated field data is loaded correct
     *
     * Performs assertions on the loaded field after it has been updated,
     * mainly checking that the $field->value->externalData is loaded
     * correctly. If the loading of external data manipulates other aspects of
     * $field, their correctness also needs to be asserted. Make sure you
     * implement this method agnostic to the used SPI\Persistence
     * implementation!
     */
    public function assertUpdatedFieldDataCorrect( Field $field )
    {
        $this->assertEquals(
            $this->getUpdatedValue()->externalData,
            $field->value->externalData
        );

        $this->assertNull( $field->value->data );
        $this->assertNull( $field->value->sortKey );
    }
}
