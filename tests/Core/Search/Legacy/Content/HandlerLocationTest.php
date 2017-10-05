<?php

namespace Netgen\TagsBundle\Tests\Core\Search\Legacy\Content;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper;
use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\Core\Search\Legacy\Content;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler as CommonCriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler as CommonSortClauseHandler;
use eZ\Publish\Core\Search\Legacy\Content\Location\Gateway\SortClauseHandler as LocationSortClauseHandler;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion;
use Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\Tags\TagId as TagIdCriterionHandler;
use Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\Tags\TagKeyword as TagKeywordCriterionHandler;

/**
 * Test case for legacy location search handler with Tags criteria.
 *
 * @todo Test with criterion target
 * @todo Test TagKeyword criterion with languages/translations
 */
class HandlerLocationTest extends LanguageAwareTestCase
{
    protected static $setUp = false;

    /**
     * Only set up once for these read only tests on a large fixture.
     *
     * Skipping the reset-up, since setting up for these tests takes quite some
     * time, which is not required to spent, since we are only reading from the
     * database anyways.
     */
    public function setUp()
    {
        if (!self::$setUp) {
            parent::setUp();
            $this->insertDatabaseFixture(__DIR__ . '/../../../../../vendor/ezsystems/ezpublish-kernel/eZ/Publish/Core/Search/Legacy/Tests/_fixtures/full_dump.php');
            self::$setUp = $this->handler;

            $handler = $this->getDatabaseHandler();

            $schema = __DIR__ . '/../../../../_fixtures/schema/schema.' . $this->db . '.sql';

            $queries = array_filter(preg_split('(;\\s*$)m', file_get_contents($schema)));
            foreach ($queries as $query) {
                $handler->exec($query);
            }

            $this->insertDatabaseFixture(__DIR__ . '/../../../../_fixtures/tags_tree.php');
            $this->insertDatabaseFixture(__DIR__ . '/../../../../_fixtures/object_attributes.php');
            $this->insertDatabaseFixture(__DIR__ . '/../../../../_fixtures/class_attributes.php');
        } else {
            $this->handler = self::$setUp;
        }
    }

    public function testTagIdFilter()
    {
        $this->assertSearchResults(
            array(59, 62),
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\TagId(40),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\Location\Id()),
                    )
                )
            )
        );
    }

    public function testTagIdFilterWithTarget()
    {
        $this->assertSearchResults(
            array(59),
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\TagId(61),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\Location\Id()),
                    )
                )
            )
        );
    }

    public function testTagIdFilterIn()
    {
        $this->assertSearchResults(
            array(59, 62, 63),
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\TagId(array(40, 41)),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\Location\Id()),
                    )
                )
            )
        );
    }

    public function testTagIdFilterWithLogicalAnd()
    {
        $this->assertSearchResults(
            array(59),
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Query\Criterion\LogicalAnd(
                            array(
                                new Criterion\TagId(16),
                                new Criterion\TagId(40),
                            )
                        ),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\Location\Id()),
                    )
                )
            )
        );
    }

    public function testTagKeywordFilter()
    {
        $this->assertSearchResults(
            array(59, 62),
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\TagKeyword(Query\Criterion\Operator::EQ, 'eztags'),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                )
            )
        );
    }

    public function testTagKeywordFilterWithTarget()
    {
        $this->assertSearchResults(
            array(59),
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\TagKeyword(Query\Criterion\Operator::EQ, 'template', 'tags'),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                )
            )
        );
    }

    public function testTagKeywordFilterIn()
    {
        $this->assertSearchResults(
            array(59, 62, 63),
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\TagKeyword(Query\Criterion\Operator::IN, array('eztags', 'cms')),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                )
            )
        );
    }

    public function testTagKeywordFilterInWithLogicalAnd()
    {
        $this->assertSearchResults(
            array(59),
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Query\Criterion\LogicalAnd(
                            array(
                                new Criterion\TagKeyword(Query\Criterion\Operator::EQ, 'mobile'),
                                new Criterion\TagKeyword(Query\Criterion\Operator::EQ, 'eztags'),
                            )
                        ),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                )
            )
        );
    }

    public function testTagKeywordFilterLike()
    {
        $this->assertSearchResults(
            array(59, 60, 61, 62),
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\TagKeyword(Query\Criterion\Operator::LIKE, '%e%'),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                )
            )
        );
    }

    /**
     * Assert search results.
     *
     * @param int[] $expectedIds
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     */
    protected function assertSearchResults($expectedIds, $searchResult)
    {
        $ids = array_map(
            function ($hit) {
                return $hit->valueObject->id;
            },
            $searchResult->searchHits
        );

        sort($ids);

        $this->assertEquals($expectedIds, $ids);
    }

    /**
     * Returns the content search handler to test.
     *
     * This method returns a fully functional search handler to perform tests
     * on.
     *
     * @return \eZ\Publish\Core\Search\Legacy\Content\Handler
     */
    protected function getContentSearchHandler()
    {
        return new Content\Handler(
            $this->createMock(Content\Gateway::class),
            new Content\Location\Gateway\DoctrineDatabase(
                $this->getDatabaseHandler(),
                new CriteriaConverter(
                    array(
                        new TagIdCriterionHandler($this->getDatabaseHandler()),
                        new TagKeywordCriterionHandler($this->getDatabaseHandler()),
                        new CommonCriterionHandler\ContentId($this->getDatabaseHandler()),
                        new CommonCriterionHandler\LogicalAnd($this->getDatabaseHandler()),
                        new CommonCriterionHandler\MatchAll($this->getDatabaseHandler()),
                    )
                ),
                new SortClauseConverter(
                    array(
                        new LocationSortClauseHandler\Location\Id($this->getDatabaseHandler()),
                        new CommonSortClauseHandler\ContentId($this->getDatabaseHandler()),
                    )
                ),
                $this->getLanguageHandler()
            ),
            $this->createMock(Content\WordIndexer\Gateway::class),
            $this->createMock(ContentMapper::class),
            $this->getLocationMapperMock(),
            $this->getLanguageHandler(),
            $this->createMock(Content\Mapper\FullTextMapper::class)
        );
    }

    /**
     * Returns a location mapper mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected function getLocationMapperMock()
    {
        $mapperMock = $this->createMock(
            LocationMapper::class,
            array('createLocationsFromRows')
        );
        $mapperMock
            ->expects($this->any())
            ->method('createLocationsFromRows')
            ->with($this->isType('array'))
            ->will(
                $this->returnCallback(
                    function ($rows) {
                        $locations = array();
                        foreach ($rows as $row) {
                            $locationId = (int) $row['node_id'];
                            if (!isset($locations[$locationId])) {
                                $locations[$locationId] = new SPILocation();
                                $locations[$locationId]->id = $locationId;
                            }
                        }

                        return array_values($locations);
                    }
                )
            );

        return $mapperMock;
    }
}
