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

        $schema = __DIR__ . "/../../../../../_fixtures/schema/schema." . $this->db . ".sql";

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
                // Update PostgreSQL sequences
                $handler = $this->getDatabaseHandler();

                $queries = array_filter( preg_split( "(;\\s*$)m", file_get_contents( __DIR__ . "/../../../../../schema/_fixtures/setval.pgsql.sql" ) ) );
                foreach ( $queries as $query )
                {
                    $handler->exec( $query );
                }

                break;
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
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
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
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
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
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
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
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->getBasicTagDataByRemoteId( "unknown" );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::getChildren
     */
    public function testGetChildren()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getChildren( 16 );

        $this->assertCount( 6, $data );
        $this->assertEquals( 15, $data[0]["id"] );
        $this->assertEquals( 18, $data[1]["id"] );
        $this->assertEquals( 19, $data[2]["id"] );
        $this->assertEquals( 20, $data[3]["id"] );
        $this->assertEquals( 71, $data[4]["id"] );
        $this->assertEquals( 72, $data[5]["id"] );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::getChildrenCount
     */
    public function testGetChildrenCount()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $tagsCount = $handler->getChildrenCount( 16 );

        $this->assertEquals( 6, $tagsCount );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::getSynonyms
     */
    public function testGetSynonyms()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getSynonyms( 16 );

        $this->assertCount( 2, $data );
        $this->assertEquals( 95, $data[0]["id"] );
        $this->assertEquals( 96, $data[1]["id"] );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::getSynonymCount
     */
    public function testGetSynonymCount()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $tagsCount = $handler->getSynonymCount( 16 );

        $this->assertEquals( 2, $tagsCount );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::getRelatedContentIds
     */
    public function testGetRelatedContentIds()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/content_objects.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getRelatedContentIds( 40 );

        $this->assertCount( 3, $data );
        $this->assertEquals( array( 57, 58, 59 ), $data );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::getRelatedContentCount
     */
    public function testGetRelatedContentCount()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/content_objects.php" );
        $handler = $this->getTagsGateway();
        $contentCount = $handler->getRelatedContentCount( 40 );

        $this->assertEquals( 3, $contentCount );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::create
     */
    public function testCreate()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
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
                array( 97, 40, 0, "New tag", 4, "/8/7/40/97/", "newRemoteId" )
            ),
            // 97 is the next inserted ID
            $query
                ->select( "id", "parent_id", "main_tag_id", "keyword", "depth", "path_string", "remote_id" )
                ->from( "eztags" )
                ->where( $query->expr->eq( "id", 97 ) )
        );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::update
     */
    public function testUpdate()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
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
        );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::createSynonym
     */
    public function testCreateSynonym()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->createSynonym(
            "New synonym",
            array(
                "id" => 40,
                "parent_id" => 7,
                "depth" => 3,
                "path_string" => "/8/7/40/"
            )
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 97, 7, 40, "New synonym", 3, "/8/7/97/" )
            ),
            // 97 is the next inserted ID
            $query
                ->select( "id", "parent_id", "main_tag_id", "keyword", "depth", "path_string" )
                ->from( "eztags" )
                ->where( $query->expr->eq( "id", 97 ) )
        );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::convertToSynonym
     */
    public function testConvertToSynonym()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->convertToSynonym(
            80,
            array(
                "id" => 40,
                "parent_id" => 7,
                "depth" => 3,
                "path_string" => "/8/7/40/"
            )
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 80, 7, 40, "fetch", 3, "/8/7/80/" )
            ),
            $query
                ->select( "id", "parent_id", "main_tag_id", "keyword", "depth", "path_string" )
                ->from( "eztags" )
                ->where( $query->expr->eq( "id", 80 ) )
        );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::transferTagAttributeLinks
     */
    public function testTransferTagAttributeLinks()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();

        $handler->transferTagAttributeLinks( 40, 42 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 1285, 42, 242, 1, 58 ),
                array( 1286, 42, 342, 1, 59 ),
                array( 1287, 42, 142, 1, 57 )
            ),
            $query
                ->select( "id", "keyword_id", "objectattribute_id", "objectattribute_version", "object_id" )
                ->from( "eztags_attribute_link" )
                ->where( $query->expr->in( "id", array( 1284, 1285, 1286, 1287 ) ) )
        );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::moveSubtree
     */
    public function testMoveSubtree()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->moveSubtree(
            array(
                "id" => 7,
                "path_string" => "/8/7/"
            ),
            array(
                "id" => 78,
                "path_string" => "/8/78/"
            )
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 7, 78, 3, "/8/78/7/" ),
                array( 13, 7, 4, "/8/78/7/13/" ),
                array( 14, 7, 4, "/8/78/7/14/" ),
                array( 27, 7, 4, "/8/78/7/27/" ),
                array( 40, 7, 4, "/8/78/7/40/" ),
                array( 53, 7, 4, "/8/78/7/53/" ),
                array( 54, 7, 4, "/8/78/7/54/" ),
                array( 55, 7, 4, "/8/78/7/55/" )
            ),
            $query
                ->select( "id", "parent_id", "depth", "path_string" )
                ->from( "eztags" )
                ->where( $query->expr->in( "id", array( 7, 13, 14, 27, 40, 53, 54, 55 ) ) )
        );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::deleteTag
     */
    public function testDeleteTag()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
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
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array()
            ),
            $query
                ->select( "keyword_id" )
                ->from( "eztags_attribute_link" )
                ->where( $query->expr->in( "keyword_id", array( 7, 13, 14, 27, 40, 53, 54, 55 ) ) )
        );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\EzcDatabase::updateSubtreeModificationTime
     */
    public function testUpdateSubtreeModificationTime()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
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
        );
    }
}
