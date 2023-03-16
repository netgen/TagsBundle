<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Core\Search\Legacy\Content;

use Doctrine\DBAL\Connection;
use Ibexa\Contracts\Core\Persistence\Content as ContentObject;
use Ibexa\Contracts\Core\Persistence\Content\ContentInfo;
use Ibexa\Contracts\Core\Persistence\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry;
use Ibexa\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper;
use Ibexa\Core\Persistence\Legacy\Content\Mapper;
use Ibexa\Core\Search\Legacy\Content;
use Ibexa\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use Ibexa\Core\Search\Legacy\Content\Handler;
use Ibexa\Core\Search\Legacy\Content\Location\Gateway;
use Ibexa\Tests\Core\Persistence\Legacy\Content\LanguageAwareTestCase;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion;
use Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\Tags\TagId as TagIdCriterionHandler;
use Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\Tags\TagKeyword as TagKeywordCriterionHandler;
use PHPUnit\Framework\MockObject\MockObject;

use function array_filter;
use function array_map;
use function array_values;
use function file_get_contents;
use function preg_split;
use function sort;

/**
 * @todo Test with criterion target
 * @todo Test TagKeyword criterion with languages/translations
 */
final class HandlerContentTest extends LanguageAwareTestCase
{
    private static Connection $dbConnection;

    private ConverterRegistry $fieldRegistry;

    /**
     * Only set up once for these read only tests on a large fixture.
     *
     * Skipping the reset-up, since setting up for these tests takes quite some
     * time, which is not required to spent, since we are only reading from the
     * database anyways.
     */
    protected function setUp(): void
    {
        if (!isset(self::$dbConnection)) {
            parent::setUp();
            $this->insertDatabaseFixture(__DIR__ . '/../../../../../vendor/ibexa/core/tests/lib/Search/Legacy/_fixtures/full_dump.php');
            self::$dbConnection = $this->getDatabaseConnection();

            $dbConnection = $this->getDatabaseConnection();

            $schema = __DIR__ . '/../../../../_fixtures/schema/schema.' . $this->db . '.sql';

            /** @var string[] $queries */
            $queries = preg_split('(;\\s*$)m', (string) file_get_contents($schema));
            $queries = array_filter($queries);
            foreach ($queries as $query) {
                $dbConnection->exec($query);
            }

            $this->insertDatabaseFixture(__DIR__ . '/../../../../_fixtures/tags_tree.php');
            $this->insertDatabaseFixture(__DIR__ . '/../../../../_fixtures/object_attributes.php');
            $this->insertDatabaseFixture(__DIR__ . '/../../../../_fixtures/class_attributes.php');
        }

        $this->fieldRegistry = new ConverterRegistry();
    }

    public function testTagIdFilter(): void
    {
        $this->assertSearchResults(
            [57, 60],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\TagId(40),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testTagIdFilterWithTarget(): void
    {
        $this->assertSearchResults(
            [57],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\TagId(61, 'tags'),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testTagIdFilterIn(): void
    {
        $this->assertSearchResults(
            [57, 60, 61],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\TagId([40, 41]),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testTagIdFilterWithLogicalAnd(): void
    {
        $this->assertSearchResults(
            [57],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Query\Criterion\LogicalAnd(
                            [
                                new Criterion\TagId(16),
                                new Criterion\TagId(40),
                            ]
                        ),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testTagKeywordFilter(): void
    {
        $this->assertSearchResults(
            [57, 60],
            $this->getContentSearchHandler()->findContent(
                new Query(
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
            [57],
            $this->getContentSearchHandler()->findContent(
                new Query(
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
            [57, 60, 61],
            $this->getContentSearchHandler()->findContent(
                new Query(
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
            [57],
            $this->getContentSearchHandler()->findContent(
                new Query(
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
            [57, 58, 59, 60],
            $this->getContentSearchHandler()->findContent(
                new Query(
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
        $result = array_map(
            static fn (SearchHit $hit): int => $hit->valueObject->id,
            $searchResult->searchHits
        );

        sort($result);

        self::assertSame($expectedIds, $result);
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
            new Content\Gateway\DoctrineDatabase(
                $this->getDatabaseConnection(),
                new Content\Common\Gateway\CriteriaConverter(
                    [
                        new TagIdCriterionHandler(
                            $this->getDatabaseConnection()
                        ),
                        new TagKeywordCriterionHandler(
                            $this->getDatabaseConnection()
                        ),
                        new CriterionHandler\ContentId(
                            $this->getDatabaseConnection()
                        ),
                        new CriterionHandler\LogicalAnd(
                            $this->getDatabaseConnection()
                        ),
                        new CriterionHandler\MatchAll(
                            $this->getDatabaseConnection()
                        ),
                    ]
                ),
                new Content\Common\Gateway\SortClauseConverter(
                    [
                        new Content\Common\Gateway\SortClauseHandler\ContentId($this->getDatabaseConnection()),
                    ]
                ),
                $this->getLanguageHandler()
            ),
            $this->createMock(Gateway::class),
            $this->createMock(Content\WordIndexer\Gateway::class),
            $this->getContentMapperMock(),
            $this->createMock(LocationMapper::class),
            $this->getLanguageHandler(),
            $this->createMock(Content\Mapper\FullTextMapper::class)
        );
    }

    private function getContentMapperMock(): MockObject&Mapper
    {
        $mapperMock = $this->getMockBuilder(Mapper::class)
            ->onlyMethods(['extractContentFromRows'])
            ->setConstructorArgs(
                [
                    $this->fieldRegistry,
                    $this->getLanguageHandler(),
                ]
            )
            ->getMock();

        $mapperMock->expects(self::any())
            ->method('extractContentFromRows')
            ->with(self::isType('array'))
            ->willReturnCallback(
                static function (array $rows): array {
                    $contentObjects = [];
                    foreach ($rows as $row) {
                        $contentId = (int) $row['ezcontentobject_id'];
                        if (!isset($contentObjects[$contentId])) {
                            $contentObjects[$contentId] = new ContentObject();
                            $contentObjects[$contentId]->versionInfo = new VersionInfo();
                            $contentObjects[$contentId]->versionInfo->contentInfo = new ContentInfo();
                            $contentObjects[$contentId]->versionInfo->contentInfo->id = $contentId;
                        }
                    }

                    return array_values($contentObjects);
                }
            );

        return $mapperMock;
    }
}
