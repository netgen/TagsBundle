<?php

namespace Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Tags\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;
use Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Content\LanguageHandlerMock;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;

/**
 * Test case for Tags Legacy gateway
 */
class DoctrineDatabaseTest extends TestCase
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
     * @return \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase
     */
    protected function getTagsGateway()
    {
        $dbHandler = $this->getDatabaseHandler();
        return new DoctrineDatabase(
            $dbHandler,
            new LanguageHandlerMock(),
            new MaskGenerator(
                new LanguageHandlerMock()
            )
        );
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
            array( "remote_id", "182be0c5cdcd5072bb1864cdee4d3d6e" ),
            array( "main_language_id", 8 ),
            array( "language_mask", "8" )
        );
    }

    /**
     * @return array
     */
    public static function getLoadFullTagValues()
    {
        return array(
            array( "eztags_id", 40 ),
            array( "eztags_parent_id", 7 ),
            array( "eztags_main_tag_id", 0 ),
            array( "eztags_keyword", "eztags" ),
            array( "eztags_depth", 3 ),
            array( "eztags_path_string", "/8/7/40/" ),
            array( "eztags_modified", 1308153110 ),
            array( "eztags_remote_id", "182be0c5cdcd5072bb1864cdee4d3d6e" ),
            array( "eztags_main_language_id", 8 ),
            array( "eztags_language_mask", "8" ),
            array( "eztags_keyword_keyword", "eztags" ),
            array( "eztags_keyword_locale", "eng-GB" )
        );
    }

    /**
     * @dataProvider getLoadTagValues
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getBasicTagData
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
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getBasicTagData
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
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getBasicTagDataByRemoteId
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
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getBasicTagDataByRemoteId
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testGetBasicTagDataByRemoteIdThrowsNotFoundException()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->getBasicTagDataByRemoteId( "unknown" );
    }

    /**
     * @dataProvider getLoadFullTagValues
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagData
     *
     * @param string $field
     * @param mixed $value
     */
    public function testGetFullTagData( $field, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getFullTagData( 40 );

        $this->assertEquals(
            $value,
            $data[0][$field],
            "Value in property $field not as expected."
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagData
     */
    public function testGetNonExistentFullTagData()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getFullTagData( 999 );

        $this->assertEquals( array(), $data );
    }

    /**
     * @dataProvider getLoadFullTagValues
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagDataByRemoteId
     *
     * @param string $field
     * @param mixed $value
     */
    public function testGetFullTagDataByRemoteId( $field, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getFullTagDataByRemoteId( "182be0c5cdcd5072bb1864cdee4d3d6e" );

        $this->assertEquals(
            $value,
            $data[0][$field],
            "Value in property $field not as expected."
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagDataByRemoteId
     */
    public function testGetNonExistentFullTagDataByRemoteId()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getFullTagDataByRemoteId( "unknown" );

        $this->assertEquals( array(), $data );
    }

    /**
     * @dataProvider getLoadFullTagValues
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagData
     *
     * @param string $field
     * @param mixed $value
     */
    public function testGetFullTagDataByKeywordIdAndParentId( $field, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getFullTagDataByKeywordAndParentId( "eztags", 7 );

        $this->assertEquals(
            $value,
            $data[0][$field],
            "Value in property $field not as expected."
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagData
     */
    public function testGetNonExistentFullTagDataByKeywordIdAndParentId()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getFullTagDataByKeywordAndParentId( "unknown", 999 );

        $this->assertEquals( array(), $data );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getChildren
     */
    public function testGetChildren()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getChildren( 16 );

        $this->assertCount( 6, $data );
        $this->assertEquals( 15, $data[0]["eztags_id"] );
        $this->assertEquals( 18, $data[1]["eztags_id"] );
        $this->assertEquals( 19, $data[2]["eztags_id"] );
        $this->assertEquals( 20, $data[3]["eztags_id"] );
        $this->assertEquals( 71, $data[4]["eztags_id"] );
        $this->assertEquals( 72, $data[5]["eztags_id"] );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getChildrenCount
     */
    public function testGetChildrenCount()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $tagsCount = $handler->getChildrenCount( 16 );

        $this->assertEquals( 6, $tagsCount );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getTagsByKeyword
     */
    public function testGetTagsByKeyword()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getTagsByKeyword( "eztags", "eng-GB" );

        $this->assertCount( 2, $data );
        $this->assertEquals( "eztags", $data[0]["eztags_keyword_keyword"] );
        $this->assertEquals( "eztags", $data[1]["eztags_keyword_keyword"] );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getTagsByKeywordCount
     */
    public function testGetTagsByKeywordCount()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $tagsCount = $handler->getTagsByKeywordCount( "eztags", "eng-GB" );

        $this->assertEquals( 2, $tagsCount );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getSynonyms
     */
    public function testGetSynonyms()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getSynonyms( 16 );

        $this->assertCount( 2, $data );
        $this->assertEquals( 95, $data[0]["eztags_id"] );
        $this->assertEquals( 96, $data[1]["eztags_id"] );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getSynonymCount
     */
    public function testGetSynonymCount()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $tagsCount = $handler->getSynonymCount( 16 );

        $this->assertEquals( 2, $tagsCount );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getRelatedContentIds
     */
    public function testGetRelatedContentIds()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/content_objects.php" );
        $handler = $this->getTagsGateway();
        $data = $handler->getRelatedContentIds( 16 );

        $this->assertCount( 3, $data );
        $this->assertEquals( array( 57, 58, 59 ), $data );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getRelatedContentCount
     */
    public function testGetRelatedContentCount()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/content_objects.php" );
        $handler = $this->getTagsGateway();
        $contentCount = $handler->getRelatedContentCount( 16 );

        $this->assertEquals( 3, $contentCount );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::moveSynonym
     */
    public function testMoveSynonym()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->moveSynonym(
            95,
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
                array( 95, 7, 40, "project 2", 3, "/8/7/95/", "fe9fc289c3ff0af142b6d3bead98a924" )
            ),
            $query
                ->select( "id", "parent_id", "main_tag_id", "keyword", "depth", "path_string", "remote_id" )
                ->from( "eztags" )
                ->where( $query->expr->eq( "id", 95 ) )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::create
     */
    public function testCreate()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->create(
            new CreateStruct(
                array(
                    "parentTagId" => 40,
                    "mainLanguageCode" => "eng-GB",
                    "keywords" => array( "eng-GB" => "New tag" ),
                    "remoteId" => "newRemoteId",
                    "alwaysAvailable" => false
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
                array( 97, 40, 0, "New tag", 4, "/8/7/40/97/", "newRemoteId", 8, 8 )
            ),
            // 97 is the next inserted ID
            $query
                ->select( "id", "parent_id", "main_tag_id", "keyword", "depth", "path_string", "remote_id", "main_language_id", "language_mask" )
                ->from( "eztags" )
                ->where( $query->expr->eq( "id", 97 ) )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::create
     */
    public function testCreateWithNoParent()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->create(
            new CreateStruct(
                array(
                    "parentTagId" => 0,
                    "mainLanguageCode" => "eng-GB",
                    "keywords" => array( "eng-GB" => "New tag" ),
                    "remoteId" => "newRemoteId",
                    "alwaysAvailable" => false
                )
            )
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 97, 0, 0, "New tag", 1, "/97/", "newRemoteId", 8, 8 )
            ),
            // 97 is the next inserted ID
            $query
                ->select( "id", "parent_id", "main_tag_id", "keyword", "depth", "path_string", "remote_id", "main_language_id", "language_mask" )
                ->from( "eztags" )
                ->where( $query->expr->eq( "id", 97 ) )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::update
     */
    public function testUpdate()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->update(
            new UpdateStruct(
                array(
                    "keywords" => array( "eng-GB" => "Updated tag US", "eng-US" => "Updated tag" ),
                    "remoteId" => "updatedRemoteId",
                    "mainLanguageCode" => "eng-US",
                    "alwaysAvailable" => true
                )
            ),
            40
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 40, 7, 0, "Updated tag", 3, "/8/7/40/", 1308153110, "updatedRemoteId", 2, 11 )
            ),
            $query
                ->select( "*" )
                ->from( "eztags" )
                ->where( $query->expr->eq( "id", 40 ) )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createSynonym
     */
    public function testCreateSynonym()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();
        $handler->createSynonym(
            new SynonymCreateStruct(
                array(
                    "mainTagId" => 40,
                    "mainLanguageCode" => "eng-GB",
                    "keywords" => array( "eng-GB" => "New synonym" ),
                    "remoteId" => "newRemoteId",
                    "alwaysAvailable" => true
                )
            ),
            array(
                "parent_id" => 7,
                "depth" => 3,
                "path_string" => "/8/7/40/"
            )
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 97, 7, 40, "New synonym", 3, "/8/7/97/", "newRemoteId", 8, 9 )
            ),
            // 97 is the next inserted ID
            $query
                ->select( "id", "parent_id", "main_tag_id", "keyword", "depth", "path_string", "remote_id", "main_language_id", "language_mask" )
                ->from( "eztags" )
                ->where( $query->expr->eq( "id", 97 ) )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::convertToSynonym
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
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::transferTagAttributeLinks
     */
    public function testTransferTagAttributeLinks()
    {
        $this->insertDatabaseFixture( __DIR__ . "/../../../../../_fixtures/tags_tree.php" );
        $handler = $this->getTagsGateway();

        $handler->transferTagAttributeLinks( 16, 40 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 1285, 40, 242, 1, 58 ),
                array( 1286, 40, 342, 1, 59 ),
                array( 1287, 40, 142, 1, 57 )
            ),
            $query
                ->select( "id", "keyword_id", "objectattribute_id", "objectattribute_version", "object_id" )
                ->from( "eztags_attribute_link" )
                ->where( $query->expr->in( "id", array( 1284, 1285, 1286, 1287 ) ) )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::moveSubtree
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
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::deleteTag
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
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::updateSubtreeModificationTime
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
