<?php

namespace EzSystems\TagsBundle\Tests\Core\Persistence\Legacy\Tags\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase;
use EzSystems\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use EzSystems\TagsBundle\SPI\Persistence\Tags\UpdateStruct;

/**
 * Test case for Tags Legacy gateway
 */
class EzcDatabaseTest extends TestCase
{
    /**
     * Sets up the test suite
     */
    public function setUp()
    {
        parent::setUp();

        $handler = $this->getDatabaseHandler();

        $schema = __DIR__ . "/../../../Legacy/_fixtures/schema." . $this->db . ".sql";

        $queries = array_filter( preg_split( "(;\\s*$)m", file_get_contents( $schema ) ) );
        foreach ( $queries as $query )
        {
            $handler->exec( $query );
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

                $queries = array_filter( preg_split( "(;\\s*$)m", file_get_contents( __DIR__ . "/../../../Legacy/_fixtures/setval.pgsql.sql" ) ) );
                foreach ( $queries as $query )
                {
                    $handler->exec( $query );
                }
            }
        }
    }

    /**
     * Returns gateway implementation for legacy storage
     *
     * @return \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase
     */
    protected function getTagsGateway()
    {
        $dbHandler = $this->getDatabaseHandler();
        return new EzcDatabase( $dbHandler );
    }

    /**
     * @return array
     */
    public static function getLoadTagValues()
    {
        return array(
            array( "id", 40 ),
            array( "parent_id", 7 ),
            array( "main_tag_id", 0 ),
            array( "keyword", "eztags" ),
            array( "depth", 3 ),
            array( "path_string", "/8/7/40/" ),
            array( "modified", 1308153110 ),
            array( "remote_id", "182be0c5cdcd5072bb1864cdee4d3d6e" )
        );
    }

    /**
     * @dataProvider getLoadTagValues
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::getBasicTagData
     *
     * @param string $field
     * @param mixed $value
     */
    public function testGetBasicTagData( $field, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getBasicTagData( 40 );

        $this->assertEquals(
            $value,
            $data[$field],
            "Value in property $field not as expected."
        );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::getBasicTagData
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testGetBasicTagDataThrowsNotFoundException()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->getBasicTagData( 999 );
    }

    /**
     * @dataProvider getLoadTagValues
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::getBasicTagDataByRemoteId
     *
     * @param string $field
     * @param mixed $value
     */
    public function testGetBasicTagDataByRemoteId( $field, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getBasicTagDataByRemoteId( "182be0c5cdcd5072bb1864cdee4d3d6e" );

        $this->assertEquals(
            $value,
            $data[$field],
            "Value in property $field not as expected."
        );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::getBasicTagDataByRemoteId
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testGetBasicTagDataByRemoteIdThrowsNotFoundException()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->getBasicTagDataByRemoteId( "unknown" );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::create
     */
    public function testCreate()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->create(
            new CreateStruct(
                array(
                    "parentTagId" => 40,
                    "keyword" => "New tag",
                    "remoteId" => "newRemoteId"
                )
            ),
            array(
                "id" => 40,
                "depth" => 3,
                "path_string" => "/8/7/40/"
            )
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 95, 40, 0, "New tag", 4, "/8/7/40/95/", "newRemoteId" )
            ),
            $query
                ->select( "id", "parent_id", "main_tag_id", "keyword", "depth", "path_string", "remote_id" )
                ->from( "eztags" )
                // 95 is the next inserted ID
                ->where( $query->expr->eq( "id", 95 ) )
            )
        ;
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::update
     */
    public function testUpdate()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->update(
            new UpdateStruct(
                array(
                    "keyword" => "Updated tag",
                    "remoteId" => "updatedRemoteId"
                )
            ),
            40
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 40, 7, 0, "Updated tag", 3, "/8/7/40/", 1308153110, "updatedRemoteId" )
            ),
            $query
                ->select( "*" )
                ->from( "eztags" )
                ->where( $query->expr->eq( "id", 40 ) )
            )
        ;
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::updateSubtreeModificationTime
     */
    public function testUpdateSubtreeModificationTime()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->updateSubtreeModificationTime( "/8/7/40/", 123 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 123 ),
                array( 123 ),
                array( 123 )
            ),
            $query
                ->select( "modified" )
                ->from( "eztags" )
                ->where( $query->expr->in( "id", array( 8, 7, 40 ) ) )
            )
        ;
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::deleteTag
     */
    public function testDeleteTag()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->deleteTag( 7 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array()
            ),
            $query
                ->select( "id" )
                ->from( "eztags" )
                ->where( $query->expr->in( "id", array( 7, 13, 14, 27, 40, 53, 54, 55 ) ) )
            )
        ;

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array()
            ),
            $query
                ->select( "keyword_id" )
                ->from( "eztags_attribute_link" )
                ->where( $query->expr->in( "keyword_id", array( 7, 13, 14, 27, 40, 53, 54, 55 ) ) )
            )
        ;
    }
}
