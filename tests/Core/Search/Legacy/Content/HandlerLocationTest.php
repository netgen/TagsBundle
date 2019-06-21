<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Core\Search\Legacy\Content;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper;
use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\Core\Search\Legacy\Content;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler as CommonCriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler as CommonSortClauseHandler;
use eZ\Publish\Core\Search\Legacy\Content\Handler;
use eZ\Publish\Core\Search\Legacy\Content\Location\Gateway\SortClauseHandler as LocationSortClauseHandler;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion;
use Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\Tags\TagId as TagIdCriterionHandler;
use Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\Tags\TagKeyword as TagKeywordCriterionHandler;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @todo Test with criterion target
 * @todo Test TagKeyword criterion with languages/translations
 */
class HandlerLocationTest extends LanguageAwareTestCase
{
    private static $setUp = false;

    /**
     * Only set up once for these read only tests on a large fixture.
     *
     * Skipping the reset-up, since setting up for these tests takes quite some
     * time, which is not required to spent, since we are only reading from the
     * database anyways.
     */
    protected function setUp(): void
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

    public function testTagIdFilter(): void
    {
        $this->assertSearchResults(
            [59, 62],
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    [
                        'filter' => new Criterion\TagId(40),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\Location\Id()],
                    ]
                )
            )
        );
    }

    public function testTagIdFilterWithTarget(): void
    {
        $this->assertSearchResults(
            [59],
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    [
                        'filter' => new Criterion\TagId(61),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\Location\Id()],
                    ]
                )
            )
        );
    }

    public function testTagIdFilterIn(): void
    {
        $this->assertSearchResults(
            [59, 62, 63],
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    [
                        'filter' => new Criterion\TagId([40, 41]),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\Location\Id()],
                    ]
                )
            )
        );
    }

    public function testTagIdFilterWithLogicalAnd(): void
    {
        $this->assertSearchResults(
            [59],
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    [
                        'filter' => new Query\Criterion\LogicalAnd(
                            [
                                new Criterion\TagId(16),
                                new Criterion\TagId(40),
                            ]
                        ),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\Location\Id()],
                    ]
                )
            )
        );
    }

    public function testTagKeywordFilter(): void
    {
        $this->assertSearchResults(
            [59, 62],
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    [
                        'filter' => new Criterion\TagKeyword(Query\Criterion\Operator::EQ, 'eztags'),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testTagKeywordFilterWithTarget(): void
    {
        $this->assertSearchResults(
            [59],
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    [
                        'filter' => new Criterion\TagKeyword(Query\Criterion\Operator::EQ, 'template', 'tags'),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testTagKeywordFilterIn(): void
    {
        $this->assertSearchResults(
            [59, 62, 63],
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    [
                        'filter' => new Criterion\TagKeyword(Query\Criterion\Operator::IN, ['eztags', 'cms']),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testTagKeywordFilterInWithLogicalAnd(): void
    {
        $this->assertSearchResults(
            [59],
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    [
                        'filter' => new Query\Criterion\LogicalAnd(
                            [
                                new Criterion\TagKeyword(Query\Criterion\Operator::EQ, 'mobile'),
                                new Criterion\TagKeyword(Query\Criterion\Operator::EQ, 'eztags'),
                            ]
                        ),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testTagKeywordFilterLike(): void
    {
        $this->assertSearchResults(
            [59, 60, 61, 62],
            $this->getContentSearchHandler()->findLocations(
                new LocationQuery(
                    [
                        'filter' => new Criterion\TagKeyword(Query\Criterion\Operator::LIKE, '%e%'),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    private function assertSearchResults(array $expectedIds, SearchResult $searchResult): void
    {
        $ids = array_map(
            static function (SearchHit $hit): int {
                return $hit->valueObject->id;
            },
            $searchResult->searchHits
        );

        sort($ids);

        self::assertSame($expectedIds, $ids);
    }

    /**
     * Returns the content search handler to test.
     *
     * This method returns a fully functional search handler to perform tests
     * on.
     */
    private function getContentSearchHandler(): Handler
    {
        return new Content\Handler(
            $this->createMock(Content\Gateway::class),
            new Content\Location\Gateway\DoctrineDatabase(
                $this->getDatabaseHandler(),
                new CriteriaConverter(
                    [
                        new TagIdCriterionHandler($this->getDatabaseHandler()),
                        new TagKeywordCriterionHandler($this->getDatabaseHandler()),
                        new CommonCriterionHandler\ContentId($this->getDatabaseHandler()),
                        new CommonCriterionHandler\LogicalAnd($this->getDatabaseHandler()),
                        new CommonCriterionHandler\MatchAll($this->getDatabaseHandler()),
                    ]
                ),
                new SortClauseConverter(
                    [
                        new LocationSortClauseHandler\Location\Id($this->getDatabaseHandler()),
                        new CommonSortClauseHandler\ContentId($this->getDatabaseHandler()),
                    ]
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

    private function getLocationMapperMock(): MockObject
    {
        $mapperMock = $this->createMock(
            LocationMapper::class,
            ['createLocationsFromRows']
        );

        $mapperMock
            ->expects(self::any())
            ->method('createLocationsFromRows')
            ->with(self::isType('array'))
            ->willReturnCallback(
                static function (array $rows): array {
                    $locations = [];
                    foreach ($rows as $row) {
                        $locationId = (int) $row['node_id'];
                        if (!isset($locations[$locationId])) {
                            $locations[$locationId] = new SPILocation();
                            $locations[$locationId]->id = $locationId;
                        }
                    }

                    return array_values($locations);
                }
            );

        return $mapperMock;
    }
}
