<?php

namespace Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Tags\Gateway;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;
use Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Content\LanguageHandlerMock;

/**
 * Test case for Tags Legacy gateway.
 */
class DoctrineDatabaseTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway
     */
    protected $tagsGateway;

    protected function setUp(): void
    {
        parent::setUp();

        $handler = $this->getDatabaseHandler();

        $schema = __DIR__ . '/../../../../../_fixtures/schema/schema.' . $this->db . '.sql';

        $queries = array_filter(preg_split('(;\\s*$)m', file_get_contents($schema)));
        foreach ($queries as $query) {
            $handler->exec($query);
        }

        $this->insertDatabaseFixture(__DIR__ . '/../../../../../_fixtures/tags_tree.php');

        $this->tagsGateway = $this->getTagsGateway();
    }

    public function resetSequences(): void
    {
        parent::resetSequences();

        if ($this->db === 'pgsql') {
            // Update PostgreSQL sequences
            $handler = $this->getDatabaseHandler();

            $queries = array_filter(preg_split('(;\\s*$)m', file_get_contents(__DIR__ . '/../../../../../schema/_fixtures/setval.pgsql.sql')));
            foreach ($queries as $query) {
                $handler->exec($query);
            }
        }
    }

    public static function getLoadTagValues(): array
    {
        return [
            ['id', '40'],
            ['parent_id', '7'],
            ['main_tag_id', '0'],
            ['keyword', 'eztags'],
            ['depth', '3'],
            ['path_string', '/8/7/40/'],
            ['modified', '1308153110'],
            ['remote_id', '182be0c5cdcd5072bb1864cdee4d3d6e'],
            ['main_language_id', '8'],
            ['language_mask', '8'],
        ];
    }

    public static function getLoadFullTagValues(): array
    {
        return [
            ['eztags_id', '40'],
            ['eztags_parent_id', '7'],
            ['eztags_main_tag_id', '0'],
            ['eztags_keyword', 'eztags'],
            ['eztags_depth', '3'],
            ['eztags_path_string', '/8/7/40/'],
            ['eztags_modified', '1308153110'],
            ['eztags_remote_id', '182be0c5cdcd5072bb1864cdee4d3d6e'],
            ['eztags_main_language_id', '8'],
            ['eztags_language_mask', '8'],
            ['eztags_keyword_keyword', 'eztags'],
            ['eztags_keyword_locale', 'eng-GB'],
        ];
    }

    /**
     * @dataProvider getLoadTagValues
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::__construct
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getBasicTagData
     *
     * @param string $field
     * @param mixed $value
     */
    public function testGetBasicTagData(string $field, $value): void
    {
        $data = $this->tagsGateway->getBasicTagData(40);

        self::assertSame(
            $value,
            $data[$field],
            "Value in property {$field} not as expected."
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getBasicTagData
     */
    public function testGetBasicTagDataThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->tagsGateway->getBasicTagData(999);
    }

    /**
     * @dataProvider getLoadTagValues
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getBasicTagDataByRemoteId
     *
     * @param string $field
     * @param mixed $value
     */
    public function testGetBasicTagDataByRemoteId(string $field, $value): void
    {
        $data = $this->tagsGateway->getBasicTagDataByRemoteId('182be0c5cdcd5072bb1864cdee4d3d6e');

        self::assertSame(
            $value,
            $data[$field],
            "Value in property {$field} not as expected."
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getBasicTagDataByRemoteId
     */
    public function testGetBasicTagDataByRemoteIdThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->tagsGateway->getBasicTagDataByRemoteId('unknown');
    }

    /**
     * @dataProvider getLoadFullTagValues
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagFindQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagData
     *
     * @param string $field
     * @param mixed $value
     */
    public function testGetFullTagData(string $field, $value): void
    {
        $data = $this->tagsGateway->getFullTagData(40);

        self::assertSame(
            $value,
            $data[0][$field],
            "Value in property {$field} not as expected."
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagData
     */
    public function testGetNonExistentFullTagData(): void
    {
        $data = $this->tagsGateway->getFullTagData(999);

        self::assertSame([], $data);
    }

    /**
     * @dataProvider getLoadFullTagValues
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagFindQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagData
     *
     * @param string $field
     * @param mixed $value
     */
    public function testGetFullTagDataWithoutAlwaysAvailable(string $field, $value): void
    {
        $data = $this->tagsGateway->getFullTagData(40, ['eng-GB'], false);

        self::assertSame(
            $value,
            $data[0][$field],
            "Value in property {$field} not as expected."
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagData
     */
    public function testGetNonExistentFullTagDataWithoutAlwaysAvailable(): void
    {
        $data = $this->tagsGateway->getFullTagData(40, ['cro-HR'], false);

        self::assertSame([], $data);
    }

    /**
     * @dataProvider getLoadFullTagValues
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagFindQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagDataByRemoteId
     *
     * @param string $field
     * @param mixed $value
     */
    public function testGetFullTagDataByRemoteId(string $field, $value): void
    {
        $data = $this->tagsGateway->getFullTagDataByRemoteId('182be0c5cdcd5072bb1864cdee4d3d6e');

        self::assertSame(
            $value,
            $data[0][$field],
            "Value in property {$field} not as expected."
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagDataByRemoteId
     */
    public function testGetNonExistentFullTagDataByRemoteId(): void
    {
        $data = $this->tagsGateway->getFullTagDataByRemoteId('unknown');

        self::assertSame([], $data);
    }

    /**
     * @dataProvider getLoadFullTagValues
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagFindQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagDataByRemoteId
     *
     * @param string $field
     * @param mixed $value
     */
    public function testGetFullTagDataByRemoteIdWithoutAlwaysAvailable(string $field, $value): void
    {
        $data = $this->tagsGateway->getFullTagDataByRemoteId('182be0c5cdcd5072bb1864cdee4d3d6e', ['eng-GB'], false);

        self::assertSame(
            $value,
            $data[0][$field],
            "Value in property {$field} not as expected."
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagDataByRemoteId
     */
    public function testGetNonExistentFullTagDataByRemoteIdWithoutAlwaysAvailable(): void
    {
        $data = $this->tagsGateway->getFullTagDataByRemoteId('182be0c5cdcd5072bb1864cdee4d3d6e', ['cro-HR'], false);

        self::assertSame([], $data);
    }

    /**
     * @dataProvider getLoadFullTagValues
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagFindQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagDataByKeywordAndParentId
     *
     * @param string $field
     * @param mixed $value
     */
    public function testGetFullTagDataByKeywordIdAndParentId(string $field, $value): void
    {
        $data = $this->tagsGateway->getFullTagDataByKeywordAndParentId('eztags', 7);

        self::assertSame(
            $value,
            $data[0][$field],
            "Value in property {$field} not as expected."
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagDataByKeywordAndParentId
     */
    public function testGetNonExistentFullTagDataByKeywordIdAndParentId(): void
    {
        $data = $this->tagsGateway->getFullTagDataByKeywordAndParentId('unknown', 999);

        self::assertSame([], $data);
    }

    /**
     * @dataProvider getLoadFullTagValues
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagFindQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagDataByKeywordAndParentId
     *
     * @param string $field
     * @param mixed $value
     */
    public function testGetFullTagDataByKeywordIdAndParentIdWithoutAlwaysAvailable(string $field, $value): void
    {
        $data = $this->tagsGateway->getFullTagDataByKeywordAndParentId('eztags', 7, ['eng-GB'], false);

        self::assertSame(
            $value,
            $data[0][$field],
            "Value in property {$field} not as expected."
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getFullTagDataByKeywordAndParentId
     */
    public function testGetNonExistentFullTagDataByKeywordIdAndParentIdWithoutAlwaysAvailable(): void
    {
        $data = $this->tagsGateway->getFullTagDataByKeywordAndParentId('eztags', 7, ['cro-HR'], false);

        self::assertSame([], $data);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagFindQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getChildren
     */
    public function testGetChildren(): void
    {
        $data = $this->tagsGateway->getChildren(16);

        self::assertCount(6, $data);
        self::assertSame('20', $data[0]['eztags_id']);
        self::assertSame('15', $data[1]['eztags_id']);
        self::assertSame('72', $data[2]['eztags_id']);
        self::assertSame('71', $data[3]['eztags_id']);
        self::assertSame('18', $data[4]['eztags_id']);
        self::assertSame('19', $data[5]['eztags_id']);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagCountQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getChildrenCount
     */
    public function testGetChildrenCount(): void
    {
        $tagsCount = $this->tagsGateway->getChildrenCount(16);

        self::assertSame(6, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagFindQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getChildren
     */
    public function testGetChildrenWithoutAlwaysAvailable(): void
    {
        $data = $this->tagsGateway->getChildren(16, 0, -1, ['eng-GB'], false);

        self::assertCount(6, $data);
        self::assertSame('20', $data[0]['eztags_id']);
        self::assertSame('15', $data[1]['eztags_id']);
        self::assertSame('72', $data[2]['eztags_id']);
        self::assertSame('71', $data[3]['eztags_id']);
        self::assertSame('18', $data[4]['eztags_id']);
        self::assertSame('19', $data[5]['eztags_id']);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagCountQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getChildrenCount
     */
    public function testGetChildrenCountWithoutAlwaysAvailable(): void
    {
        $tagsCount = $this->tagsGateway->getChildrenCount(16, ['eng-GB'], false);

        self::assertSame(6, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagFindQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getChildren
     */
    public function testGetChildrenWithoutAlwaysAvailableAndWithNonExistentLanguageCode(): void
    {
        $data = $this->tagsGateway->getChildren(16, 0, -1, ['cro-HR'], false);

        self::assertCount(0, $data);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagCountQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getChildrenCount
     */
    public function testGetChildrenCountWithoutAlwaysAvailableAndWithNonExistentLanguageCode(): void
    {
        $tagsCount = $this->tagsGateway->getChildrenCount(16, ['cro-HR'], false);

        self::assertSame(0, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagFindQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getTagsByKeyword
     */
    public function testGetTagsByKeyword(): void
    {
        $data = $this->tagsGateway->getTagsByKeyword('eztags', 'eng-GB');

        self::assertCount(2, $data);
        self::assertSame('eztags', $data[0]['eztags_keyword_keyword']);
        self::assertSame('eztags', $data[1]['eztags_keyword_keyword']);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagCountQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getTagsByKeywordCount
     */
    public function testGetTagsByKeywordCount(): void
    {
        $tagsCount = $this->tagsGateway->getTagsByKeywordCount('eztags', 'eng-GB');

        self::assertSame(2, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagFindQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getTagsByKeyword
     */
    public function testGetTagsByKeywordWithoutAlwaysAvailable(): void
    {
        $data = $this->tagsGateway->getTagsByKeyword('eztags', 'eng-GB', false);

        self::assertCount(2, $data);
        self::assertSame('eztags', $data[0]['eztags_keyword_keyword']);
        self::assertSame('eztags', $data[1]['eztags_keyword_keyword']);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagCountQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getTagsByKeywordCount
     */
    public function testGetTagsByKeywordCountWithoutAlwaysAvailable(): void
    {
        $tagsCount = $this->tagsGateway->getTagsByKeywordCount('eztags', 'eng-GB', false);

        self::assertSame(2, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagFindQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getSynonyms
     */
    public function testGetSynonyms(): void
    {
        $data = $this->tagsGateway->getSynonyms(16);

        self::assertCount(2, $data);
        self::assertSame('95', $data[0]['eztags_id']);
        self::assertSame('96', $data[1]['eztags_id']);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagCountQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getSynonymCount
     */
    public function testGetSynonymCount(): void
    {
        $tagsCount = $this->tagsGateway->getSynonymCount(16);

        self::assertSame(2, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagFindQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getSynonyms
     */
    public function testGetSynonymsWithoutAlwaysAvailable(): void
    {
        $data = $this->tagsGateway->getSynonyms(16, 0, -1, ['eng-GB'], false);

        self::assertCount(2, $data);
        self::assertSame('95', $data[0]['eztags_id']);
        self::assertSame('96', $data[1]['eztags_id']);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagCountQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getSynonymCount
     */
    public function testGetSynonymCountWithoutAlwaysAvailable(): void
    {
        $tagsCount = $this->tagsGateway->getSynonymCount(16, ['eng-GB'], false);

        self::assertSame(2, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagFindQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getSynonyms
     */
    public function testGetSynonymsWithoutAlwaysAvailableAndWithNonExistentLanguageCode(): void
    {
        $data = $this->tagsGateway->getSynonyms(16, 0, -1, ['cro-HR'], false);

        self::assertCount(0, $data);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createTagCountQuery
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getSynonymCount
     */
    public function testGetSynonymCountWithoutAlwaysAvailableAndWithNonExistentLanguageCode(): void
    {
        $tagsCount = $this->tagsGateway->getSynonymCount(16, ['cro-HR'], false);

        self::assertSame(0, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getSynonymPathString
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::moveSynonym
     */
    public function testMoveSynonym(): void
    {
        $this->tagsGateway->moveSynonym(
            95,
            [
                'id' => 40,
                'parent_id' => 7,
                'depth' => 3,
                'path_string' => '/8/7/40/',
            ]
        );

        $query = $this->handler->createSelectQuery();
        self::assertQueryResult(
            [
                [95, 7, 40, 'project 2', 3, '/8/7/95/', 'fe9fc289c3ff0af142b6d3bead98a924'],
            ],
            $query
                ->select('id', 'parent_id', 'main_tag_id', 'keyword', 'depth', 'path_string', 'remote_id')
                ->from('eztags')
                ->where($query->expr->eq('id', 95))
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::create
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::generateLanguageMask
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::insertTagKeywords
     */
    public function testCreate(): void
    {
        $this->tagsGateway->create(
            new CreateStruct(
                [
                    'parentTagId' => 40,
                    'mainLanguageCode' => 'eng-GB',
                    'keywords' => ['eng-GB' => 'New tag'],
                    'remoteId' => 'newRemoteId',
                    'alwaysAvailable' => false,
                ]
            ),
            [
                'id' => 40,
                'depth' => 3,
                'path_string' => '/8/7/40/',
            ]
        );

        $query = $this->handler->createSelectQuery();
        self::assertQueryResult(
            [
                [97, 40, 0, 'New tag', 4, '/8/7/40/97/', 'newRemoteId', 8, 8],
            ],
            // 97 is the next inserted ID
            $query
                ->select('id', 'parent_id', 'main_tag_id', 'keyword', 'depth', 'path_string', 'remote_id', 'main_language_id', 'language_mask')
                ->from('eztags')
                ->where($query->expr->eq('id', 97))
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::create
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::generateLanguageMask
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::insertTagKeywords
     */
    public function testCreateWithNoParent(): void
    {
        $this->tagsGateway->create(
            new CreateStruct(
                [
                    'parentTagId' => 0,
                    'mainLanguageCode' => 'eng-GB',
                    'keywords' => ['eng-GB' => 'New tag'],
                    'remoteId' => 'newRemoteId',
                    'alwaysAvailable' => false,
                ]
            )
        );

        $query = $this->handler->createSelectQuery();
        self::assertQueryResult(
            [
                [97, 0, 0, 'New tag', 1, '/97/', 'newRemoteId', 8, 8],
            ],
            // 97 is the next inserted ID
            $query
                ->select('id', 'parent_id', 'main_tag_id', 'keyword', 'depth', 'path_string', 'remote_id', 'main_language_id', 'language_mask')
                ->from('eztags')
                ->where($query->expr->eq('id', 97))
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::generateLanguageMask
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::insertTagKeywords
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::update
     */
    public function testUpdate(): void
    {
        $this->tagsGateway->update(
            new UpdateStruct(
                [
                    'keywords' => ['eng-GB' => 'Updated tag US', 'eng-US' => 'Updated tag'],
                    'remoteId' => 'updatedRemoteId',
                    'mainLanguageCode' => 'eng-US',
                    'alwaysAvailable' => true,
                ]
            ),
            40
        );

        $query = $this->handler->createSelectQuery();
        self::assertQueryResult(
            [
                [40, 7, 0, 'Updated tag', 3, '/8/7/40/', 'updatedRemoteId', 2, 11],
            ],
            $query
                ->select('id, parent_id, main_tag_id, keyword, depth, path_string, remote_id, main_language_id, language_mask')
                ->from('eztags')
                ->where($query->expr->eq('id', 40))
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::createSynonym
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::generateLanguageMask
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getSynonymPathString
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::insertTagKeywords
     */
    public function testCreateSynonym(): void
    {
        $this->tagsGateway->createSynonym(
            new SynonymCreateStruct(
                [
                    'mainTagId' => 40,
                    'mainLanguageCode' => 'eng-GB',
                    'keywords' => ['eng-GB' => 'New synonym'],
                    'remoteId' => 'newRemoteId',
                    'alwaysAvailable' => true,
                ]
            ),
            [
                'parent_id' => 7,
                'depth' => 3,
                'path_string' => '/8/7/40/',
            ]
        );

        $query = $this->handler->createSelectQuery();
        self::assertQueryResult(
            [
                [97, 7, 40, 'New synonym', 3, '/8/7/97/', 'newRemoteId', 8, 9],
            ],
            // 97 is the next inserted ID
            $query
                ->select('id', 'parent_id', 'main_tag_id', 'keyword', 'depth', 'path_string', 'remote_id', 'main_language_id', 'language_mask')
                ->from('eztags')
                ->where($query->expr->eq('id', 97))
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::convertToSynonym
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::getSynonymPathString
     */
    public function testConvertToSynonym(): void
    {
        $this->tagsGateway->convertToSynonym(
            80,
            [
                'id' => 40,
                'parent_id' => 7,
                'depth' => 3,
                'path_string' => '/8/7/40/',
            ]
        );

        $query = $this->handler->createSelectQuery();
        self::assertQueryResult(
            [
                [80, 7, 40, 'fetch', 3, '/8/7/80/'],
            ],
            $query
                ->select('id', 'parent_id', 'main_tag_id', 'keyword', 'depth', 'path_string')
                ->from('eztags')
                ->where($query->expr->eq('id', 80))
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::transferTagAttributeLinks
     */
    public function testTransferTagAttributeLinks(): void
    {
        $this->tagsGateway->transferTagAttributeLinks(16, 40);

        $query = $this->handler->createSelectQuery();
        self::assertQueryResult(
            [
                [1285, 40, 242, 1, 58],
                [1286, 40, 342, 1, 59],
                [1287, 40, 142, 1, 57],
            ],
            $query
                ->select('id', 'keyword_id', 'objectattribute_id', 'objectattribute_version', 'object_id')
                ->from('eztags_attribute_link')
                ->where($query->expr->in('id', [1284, 1285, 1286, 1287]))
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::moveSubtree
     */
    public function testMoveSubtree(): void
    {
        $this->tagsGateway->moveSubtree(
            [
                'id' => 7,
                'path_string' => '/8/7/',
            ],
            [
                'id' => 78,
                'path_string' => '/8/78/',
            ]
        );

        $query = $this->handler->createSelectQuery();
        self::assertQueryResult(
            [
                [7, 78, 3, '/8/78/7/'],
                [13, 7, 4, '/8/78/7/13/'],
                [14, 7, 4, '/8/78/7/14/'],
                [27, 7, 4, '/8/78/7/27/'],
                [40, 7, 4, '/8/78/7/40/'],
                [53, 7, 4, '/8/78/7/53/'],
                [54, 7, 4, '/8/78/7/54/'],
                [55, 7, 4, '/8/78/7/55/'],
            ],
            $query
                ->select('id', 'parent_id', 'depth', 'path_string')
                ->from('eztags')
                ->where($query->expr->in('id', [7, 13, 14, 27, 40, 53, 54, 55]))
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase::deleteTag
     */
    public function testDeleteTag(): void
    {
        $this->tagsGateway->deleteTag(7);

        $query = $this->handler->createSelectQuery();
        self::assertQueryResult(
            [
                [],
            ],
            $query
                ->select('id')
                ->from('eztags')
                ->where($query->expr->in('id', [7, 13, 14, 27, 40, 53, 54, 55]))
        );

        $query = $this->handler->createSelectQuery();
        self::assertQueryResult(
            [
                [],
            ],
            $query
                ->select('keyword_id')
                ->from('eztags_attribute_link')
                ->where($query->expr->in('keyword_id', [7, 13, 14, 27, 40, 53, 54, 55]))
        );
    }

    /**
     * Returns gateway implementation for legacy storage.
     */
    protected function getTagsGateway(): DoctrineDatabase
    {
        $dbHandler = $this->getDatabaseHandler();

        $languageHandlerMock = (new LanguageHandlerMock())($this);

        return new DoctrineDatabase(
            $dbHandler,
            $languageHandlerMock,
            new MaskGenerator($languageHandlerMock)
        );
    }
}
