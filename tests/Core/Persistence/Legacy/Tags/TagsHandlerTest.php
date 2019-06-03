<?php

namespace Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Tags;

use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag;
use Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;
use Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Content\LanguageHandlerMock;

/**
 * Test case for Tags Handler.
 */
class TagsHandlerTest extends TestCase
{
    /**
     * Mocked tags gateway instance.
     *
     * @var \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway
     */
    protected $gateway;

    /**
     * Mocked tags mapper instance.
     *
     * @param array $mockedMethods
     *
     * @var \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper
     */
    protected $mapper;

    /**
     * @var \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler
     */
    protected $tagsHandler;

    public function setUp()
    {
        $this->tagsHandler = $this->getTagsHandler();
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::__construct
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::load
     */
    public function testLoad()
    {
        $this->gateway
            ->expects(self::once())
            ->method('getFullTagData')
            ->with(42)
            ->willReturn(
                [
                    [
                        'eztags_id' => 42,
                    ],
                ]
            );

        $this->mapper
            ->expects(self::once())
            ->method('extractTagListFromRows')
            ->with([['eztags_id' => 42]])
            ->willReturn([new Tag(['id' => 42])]);

        $tag = $this->tagsHandler->load(42);

        self::assertInstanceOf(
            Tag::class,
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::__construct
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::load
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadThrowsNotFoundException()
    {
        $this->gateway
            ->expects(self::once())
            ->method('getFullTagData')
            ->with(42)
            ->willReturn([]);

        $this->mapper
            ->expects(self::never())
            ->method('extractTagListFromRows');

        $this->tagsHandler->load(42);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadTagInfo
     */
    public function testLoadTagInfo()
    {
        $this->gateway
            ->expects(self::once())
            ->method('getBasicTagData')
            ->with(42)
            ->willReturn(
                [
                    'id' => 42,
                ]
            );

        $this->mapper
            ->expects(self::once())
            ->method('createTagInfoFromRow')
            ->with(['id' => 42])
            ->willReturn(new TagInfo(['id' => 42]));

        $tagInfo = $this->tagsHandler->loadTagInfo(42);

        self::assertInstanceOf(
            TagInfo::class,
            $tagInfo
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadByRemoteId
     */
    public function testLoadByRemoteId()
    {
        $this->gateway
            ->expects(self::once())
            ->method('getFullTagDataByRemoteId')
            ->with('abcdef')
            ->willReturn(
                [
                    [
                        'eztags_remote_id' => 'abcdef',
                    ],
                ]
            );

        $this->mapper
            ->expects(self::once())
            ->method('extractTagListFromRows')
            ->with([['eztags_remote_id' => 'abcdef']])
            ->willReturn([new Tag(['remoteId' => 'abcdef'])]);

        $tag = $this->tagsHandler->loadByRemoteId('abcdef');

        self::assertInstanceOf(
            Tag::class,
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadByRemoteId
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadByRemoteIdThrowsNotFoundException()
    {
        $this->gateway
            ->expects(self::once())
            ->method('getFullTagDataByRemoteId')
            ->with('abcdef')
            ->willReturn([]);

        $this->mapper
            ->expects(self::never())
            ->method('extractTagListFromRows');

        $this->tagsHandler->loadByRemoteId('abcdef');
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadTagInfoByRemoteId
     */
    public function testLoadTagInfoByRemoteId()
    {
        $this->gateway
            ->expects(self::once())
            ->method('getBasicTagDataByRemoteId')
            ->with('12345')
            ->willReturn(
                [
                    'remote_id' => '12345',
                ]
            );

        $this->mapper
            ->expects(self::once())
            ->method('createTagInfoFromRow')
            ->with(['remote_id' => '12345'])
            ->willReturn(new TagInfo(['remoteId' => '12345']));

        $tagInfo = $this->tagsHandler->loadTagInfoByRemoteId('12345');

        self::assertInstanceOf(
            TagInfo::class,
            $tagInfo
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadTagByKeywordAndParentId
     */
    public function testLoadTagByKeywordAndParentId()
    {
        $this->gateway
            ->expects(self::once())
            ->method('getFullTagDataByKeywordAndParentId')
            ->with('eztags', 42)
            ->willReturn(
                [
                    [
                        'eztags_id' => 42,
                        'eztags_keyword' => 'eztags',
                        'eztags_keyword_keyword' => 'eztags',
                    ],
                ]
            );

        $this->mapper
            ->expects(self::once())
            ->method('extractTagListFromRows')
            ->with([['eztags_id' => 42, 'eztags_keyword' => 'eztags', 'eztags_keyword_keyword' => 'eztags']])
            ->willReturn([new Tag(['id' => 42, 'keywords' => ['eng-GB' => 'eztags']])]);

        $tag = $this->tagsHandler->loadTagByKeywordAndParentId('eztags', 42);

        self::assertInstanceOf(
            Tag::class,
            $tag
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadTagByKeywordAndParentId
     */
    public function testLoadTagByKeywordAndParentIdThrowsNotFoundException()
    {
        $this->gateway
            ->expects(self::once())
            ->method('getFullTagDataByKeywordAndParentId')
            ->with('unknown', 999);

        $this->tagsHandler->loadTagByKeywordAndParentId('unknown', 999);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadChildren
     */
    public function testLoadChildren()
    {
        $this->gateway
            ->expects(self::once())
            ->method('getChildren')
            ->with(42)
            ->willReturn(
                [
                    [
                        'eztags_id' => 43,
                    ],
                    [
                        'eztags_id' => 44,
                    ],
                    [
                        'eztags_id' => 45,
                    ],
                ]
            );

        $this->mapper
            ->expects(self::once())
            ->method('extractTagListFromRows')
            ->with(
                [
                    ['eztags_id' => 43],
                    ['eztags_id' => 44],
                    ['eztags_id' => 45],
                ]
            )
            ->willReturn(
                [
                    new Tag(['id' => 43]),
                    new Tag(['id' => 44]),
                    new Tag(['id' => 45]),
                ]
            );

        $tags = $this->tagsHandler->loadChildren(42);

        self::assertCount(3, $tags);

        foreach ($tags as $tag) {
            self::assertInstanceOf(
                Tag::class,
                $tag
            );
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::getChildrenCount
     */
    public function testGetChildrenCount()
    {
        $this->gateway
            ->expects(self::once())
            ->method('getChildrenCount')
            ->with(42)
            ->willReturn(3);

        $tagsCount = $this->tagsHandler->getChildrenCount(42);

        self::assertSame(3, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadTagsByKeyword
     */
    public function testLoadTagsByKeyword()
    {
        $this->gateway
            ->expects(self::once())
            ->method('getTagsByKeyword')
            ->with('eztags', 'eng-GB')
            ->willReturn(
                [
                    [
                        'eztags_keyword' => 'eztags',
                        'eztags_main_language_id' => 4,
                    ],
                    [
                        'eztags_keyword' => 'eztags',
                        'eztags_main_language_id' => 4,
                    ],
                ]
            );

        $this->mapper
            ->expects(self::once())
            ->method('extractTagListFromRows')
            ->with(
                [
                    ['eztags_keyword' => 'eztags', 'eztags_main_language_id' => 4],
                    ['eztags_keyword' => 'eztags', 'eztags_main_language_id' => 4],
                ]
            )
            ->willReturn(
                [
                    new Tag(['keywords' => ['eng-GB' => 'eztags']]),
                    new Tag(['keywords' => ['eng-GB' => 'eztags']]),
                ]
            );

        $tags = $this->tagsHandler->loadTagsByKeyword('eztags', 'eng-GB');

        self::assertCount(2, $tags);

        foreach ($tags as $tag) {
            self::assertInstanceOf(
                Tag::class,
                $tag
            );
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::getTagsByKeywordCount
     */
    public function testGetTagsByKeywordCount()
    {
        $this->gateway
            ->expects(self::once())
            ->method('getTagsByKeywordCount')
            ->with('eztags', 'eng-GB')
            ->willReturn(2);

        $tagsCount = $this->tagsHandler->getTagsByKeywordCount('eztags', 'eng-GB');

        self::assertSame(2, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadSynonyms
     */
    public function testLoadSynonyms()
    {
        $this->gateway
            ->expects(self::once())
            ->method('getSynonyms')
            ->with(42)
            ->willReturn(
                [
                    [
                        'eztags_id' => 43,
                    ],
                    [
                        'eztags_id' => 44,
                    ],
                    [
                        'eztags_id' => 45,
                    ],
                ]
            );

        $this->mapper
            ->expects(self::once())
            ->method('extractTagListFromRows')
            ->with(
                [
                    ['eztags_id' => 43],
                    ['eztags_id' => 44],
                    ['eztags_id' => 45],
                ]
            )
            ->willReturn(
                [
                    new Tag(['id' => 43]),
                    new Tag(['id' => 44]),
                    new Tag(['id' => 45]),
                ]
            );

        $tags = $this->tagsHandler->loadSynonyms(42);

        self::assertCount(3, $tags);

        foreach ($tags as $tag) {
            self::assertInstanceOf(
                Tag::class,
                $tag
            );
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::getSynonymCount
     */
    public function testGetSynonymCount()
    {
        $this->gateway
            ->expects(self::once())
            ->method('getSynonymCount')
            ->with(42)
            ->willReturn(3);

        $tagsCount = $this->tagsHandler->getSynonymCount(42);

        self::assertSame(3, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::create
     */
    public function testCreate()
    {
        $handler = $this->getTagsHandler(['load']);

        $this->gateway
            ->expects(self::once())
            ->method('getBasicTagData')
            ->with(21)
            ->willReturn(
                [
                    'id' => 21,
                    'depth' => 2,
                    'path_string' => '/1/2/',
                ]
            );

        $this->gateway
            ->expects(self::once())
            ->method('create')
            ->with(
                new CreateStruct(
                    [
                        'parentTagId' => 21,
                        'mainLanguageCode' => 'eng-GB',
                        'keywords' => ['eng-GB' => 'New tag'],
                        'remoteId' => '123456abcdef',
                        'alwaysAvailable' => true,
                    ]
                ),
                [
                    'id' => 21,
                    'depth' => 2,
                    'path_string' => '/1/2/',
                ]
            )
            ->willReturn(
                95
            );

        $handler->expects(self::once())
            ->method('load')
            ->with(95)
            ->willReturn(
                new Tag(
                        [
                            'id' => 95,
                            'parentTagId' => 21,
                            'mainTagId' => 0,
                            'keywords' => ['eng-GB' => 'New tag'],
                            'depth' => 3,
                            'pathString' => '/1/2/95/',
                            'remoteId' => '123456abcdef',
                            'alwaysAvailable' => true,
                            'mainLanguageCode' => 'eng-GB',
                            'languageIds' => [4],
                        ]
                    )
            );

        $tag = $handler->create(
            new CreateStruct(
                [
                    'parentTagId' => 21,
                    'mainLanguageCode' => 'eng-GB',
                    'keywords' => ['eng-GB' => 'New tag'],
                    'remoteId' => '123456abcdef',
                    'alwaysAvailable' => true,
                ]
            )
        );

        self::assertInstanceOf(
            Tag::class,
            $tag
        );

        $this->assertPropertiesCorrect(
            [
                'id' => 95,
                'parentTagId' => 21,
                'keywords' => ['eng-GB' => 'New tag'],
                'remoteId' => '123456abcdef',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
                'languageIds' => [4],
            ],
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::create
     */
    public function testCreateWithNoParent()
    {
        $handler = $this->getTagsHandler(['load']);

        $this->gateway
            ->expects(self::once())
            ->method('create')
            ->with(
                new CreateStruct(
                    [
                        'parentTagId' => 0,
                        'mainLanguageCode' => 'eng-GB',
                        'keywords' => ['eng-GB' => 'New tag'],
                        'remoteId' => '123456abcdef',
                        'alwaysAvailable' => true,
                    ]
                )
            )
            ->willReturn(
                95
            );

        $handler->expects(self::once())
            ->method('load')
            ->with(95)
            ->willReturn(
                new Tag(
                        [
                            'id' => 95,
                            'parentTagId' => 0,
                            'mainTagId' => 0,
                            'keywords' => ['eng-GB' => 'New tag'],
                            'depth' => 3,
                            'pathString' => '/1/2/95/',
                            'remoteId' => '123456abcdef',
                            'alwaysAvailable' => true,
                            'mainLanguageCode' => 'eng-GB',
                            'languageIds' => [4],
                        ]
                    )
            );

        $tag = $handler->create(
            new CreateStruct(
                [
                    'parentTagId' => 0,
                    'mainLanguageCode' => 'eng-GB',
                    'keywords' => ['eng-GB' => 'New tag'],
                    'remoteId' => '123456abcdef',
                    'alwaysAvailable' => true,
                ]
            )
        );

        self::assertInstanceOf(
            Tag::class,
            $tag
        );

        $this->assertPropertiesCorrect(
            [
                'id' => 95,
                'parentTagId' => 0,
                'keywords' => ['eng-GB' => 'New tag'],
                'remoteId' => '123456abcdef',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
                'languageIds' => [4],
            ],
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::update
     */
    public function testUpdate()
    {
        $handler = $this->getTagsHandler(['load']);

        $this->gateway
            ->expects(self::once())
            ->method('update')
            ->with(
                new UpdateStruct(
                    [
                        'keywords' => ['eng-US' => 'Updated tag US', 'eng-GB' => 'Updated tag'],
                        'remoteId' => '123456abcdef',
                        'mainLanguageCode' => 'eng-US',
                        'alwaysAvailable' => true,
                    ]
                ),
                40
            );

        $handler
            ->expects(self::once())
            ->method('load')
            ->with(40)
            ->willReturn(
                new Tag(
                        [
                            'id' => 40,
                            'keywords' => ['eng-US' => 'Updated tag US', 'eng-GB' => 'Updated tag'],
                            'remoteId' => '123456abcdef',
                            'mainLanguageCode' => 'eng-US',
                            'alwaysAvailable' => true,
                            'languageIds' => [2, 4],
                        ]
                    )
            );

        $tag = $handler->update(
            new UpdateStruct(
                [
                    'keywords' => ['eng-US' => 'Updated tag US', 'eng-GB' => 'Updated tag'],
                    'remoteId' => '123456abcdef',
                    'mainLanguageCode' => 'eng-US',
                    'alwaysAvailable' => true,
                ]
            ),
            40
        );

        self::assertInstanceOf(
            Tag::class,
            $tag
        );

        $this->assertPropertiesCorrect(
            [
                'id' => 40,
                'keywords' => ['eng-US' => 'Updated tag US', 'eng-GB' => 'Updated tag'],
                'remoteId' => '123456abcdef',
                'mainLanguageCode' => 'eng-US',
                'alwaysAvailable' => true,
                'languageIds' => [2, 4],
            ],
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::addSynonym
     */
    public function testAddSynonym()
    {
        $handler = $this->getTagsHandler(['load']);

        $this->gateway
            ->expects(self::once())
            ->method('getBasicTagData')
            ->with(21)
            ->willReturn(
                [
                    'id' => 21,
                    'parent_id' => 1,
                    'depth' => 2,
                    'path_string' => '/1/21/',
                ]
            );

        $this->gateway
            ->expects(self::once())
            ->method('createSynonym')
            ->with(
                new SynonymCreateStruct(
                    [
                        'mainTagId' => 21,
                        'mainLanguageCode' => 'eng-GB',
                        'keywords' => ['eng-GB' => 'New synonym'],
                        'remoteId' => '12345',
                        'alwaysAvailable' => true,
                    ]
                ),
                [
                    'id' => 21,
                    'parent_id' => 1,
                    'depth' => 2,
                    'path_string' => '/1/21/',
                ]
            )
            ->willReturn(
                95
            );

        $handler
            ->expects(self::once())
            ->method('load')
            ->with(95)
            ->willReturn(
                new Tag(
                        [
                            'id' => 95,
                            'parentTagId' => 1,
                            'mainTagId' => 21,
                            'keywords' => ['eng-GB' => 'New synonym'],
                            'depth' => 2,
                            'pathString' => '/1/95/',
                            'remoteId' => '12345',
                            'mainLanguageCode' => 'eng-GB',
                            'alwaysAvailable' => true,
                            'languageIds' => [4],
                        ]
                    )
            );

        $tag = $handler->addSynonym(
            new SynonymCreateStruct(
                [
                    'mainTagId' => 21,
                    'mainLanguageCode' => 'eng-GB',
                    'keywords' => ['eng-GB' => 'New synonym'],
                    'remoteId' => '12345',
                    'alwaysAvailable' => true,
                ]
            )
        );

        self::assertInstanceOf(
            Tag::class,
            $tag
        );

        $this->assertPropertiesCorrect(
            [
                'id' => 95,
                'parentTagId' => 1,
                'mainTagId' => 21,
                'keywords' => ['eng-GB' => 'New synonym'],
                'depth' => 2,
                'pathString' => '/1/95/',
                'remoteId' => '12345',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
                'languageIds' => [4],
            ],
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::convertToSynonym
     */
    public function testConvertToSynonym()
    {
        $handler = $this->getTagsHandler(['loadTagInfo', 'loadSynonyms', 'load']);

        $tag = new TagInfo(
            [
                'id' => 16,
                'parentTagId' => 0,
            ]
        );

        $mainTagData = [
            'id' => 66,
        ];

        $synonyms = [
            new Tag(['id' => 95]),
            new Tag(['id' => 96]),
        ];

        $handler
            ->expects(self::at(0))
            ->method('loadTagInfo')
            ->with(16)
            ->willReturn(
                $tag
            );

        $this->gateway
            ->expects(self::at(0))
            ->method('getBasicTagData')
            ->with(66)
            ->willReturn($mainTagData);

        $handler
            ->expects(self::at(1))
            ->method('loadSynonyms')
            ->with(16)
            ->willReturn($synonyms);

        foreach ($synonyms as $index => $synonym) {
            $this->gateway
                ->expects(self::at($index + 1))
                ->method('moveSynonym')
                ->with($synonym->id, $mainTagData);
        }

        $this->gateway
            ->expects(self::once())
            ->method('convertToSynonym')
            ->with(16, $mainTagData);

        $handler
            ->expects(self::at(2))
            ->method('load')
            ->with(16)
            ->willReturn(
                new Tag(
                        [
                            'id' => 16,
                        ]
                    )
            );

        $synonym = $handler->convertToSynonym(16, 66);

        self::assertInstanceOf(
            Tag::class,
            $synonym
        );

        $this->assertPropertiesCorrect(
            [
                'id' => 16,
            ],
            $synonym
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::merge
     */
    public function testMerge()
    {
        $handler = $this->getTagsHandler(['loadTagInfo', 'loadSynonyms']);

        $tags = [
            new Tag(['id' => 50]),
            new Tag(['id' => 51]),
        ];

        $handler
            ->expects(self::once())
            ->method('loadSynonyms')
            ->with(40)
            ->willReturn(
                $tags
            );

        array_push($tags, new Tag(['id' => 40]));

        foreach ($tags as $index => $tag) {
            $this->gateway
                ->expects(self::at($index * 2))
                ->method('transferTagAttributeLinks')
                ->with($tag->id, 42);

            $this->gateway
                ->expects(self::at($index * 2 + 1))
                ->method('deleteTag')
                ->with($tag->id);
        }

        $handler->merge(40, 42);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::copySubtree
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::recursiveCopySubtree
     */
    public function testCopySubtree()
    {
        self::markTestIncomplete('@TODO: Implement test for copySubtree');
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::moveSubtree
     */
    public function testMoveSubtree()
    {
        $handler = $this->getTagsHandler(['load']);

        $sourceData = [
            'id' => 42,
            'parent_id' => 21,
            'depth' => 3,
            'path_string' => '/1/21/42/',
        ];

        $destinationData = [
            'id' => 66,
            'parent_id' => 21,
            'path_string' => '/1/21/66/',
        ];

        $movedData = [
            'id' => 42,
            'parent_id' => 66,
            'depth' => 4,
            'path_string' => '/1/21/66/42/',
            'modified' => 12345,
        ];

        $this->gateway
            ->expects(self::at(0))
            ->method('getBasicTagData')
            ->with(42)
            ->willReturn($sourceData);

        $this->gateway
            ->expects(self::at(1))
            ->method('getBasicTagData')
            ->with(66)
            ->willReturn($destinationData);

        $this->gateway
            ->expects(self::once())
            ->method('moveSubtree')
            ->with($sourceData, $destinationData)
            ->willReturn($movedData);

        $handler
            ->expects(self::once())
            ->method('load')
            ->with($movedData['id'])
            ->willReturn(
                new Tag(
                        [
                            'id' => $movedData['id'],
                            'parentTagId' => $movedData['parent_id'],
                            'depth' => $movedData['depth'],
                            'pathString' => $movedData['path_string'],
                            'modificationDate' => $movedData['modified'],
                        ]
                    )
            );

        $movedTag = $handler->moveSubtree(42, 66);

        self::assertInstanceOf(
            Tag::class,
            $movedTag
        );

        $this->assertPropertiesCorrect(
            [
                'id' => $movedData['id'],
                'parentTagId' => $movedData['parent_id'],
                'depth' => $movedData['depth'],
                'pathString' => $movedData['path_string'],
                'modificationDate' => $movedData['modified'],
            ],
            $movedTag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::deleteTag
     */
    public function testDeleteTag()
    {
        $handler = $this->getTagsHandler(['loadTagInfo']);

        $handler
            ->expects(self::once())
            ->method('loadTagInfo')
            ->with(40)
            ->willReturn(
                new TagInfo(
                        [
                            'id' => 40,
                            'parentTagId' => 21,
                        ]
                    )
            );

        $this->gateway
            ->expects(self::once())
            ->method('deleteTag')
            ->with(40);

        $handler->deleteTag(40);
    }

    protected function getTagsHandler(array $mockedMethods = null)
    {
        $this->gateway = $this->createMock(Gateway::class);

        $languageHandlerMock = (new LanguageHandlerMock())($this);

        $this->mapper = $this->getMockBuilder(Mapper::class)
            ->setConstructorArgs(
                [
                    $languageHandlerMock,
                    new MaskGenerator($languageHandlerMock),
                ]
            )->getMock();

        return $this->getMockBuilder(Handler::class)
            ->setMethods($mockedMethods)
            ->setConstructorArgs([$this->gateway, $this->mapper])
            ->getMock();
    }
}
