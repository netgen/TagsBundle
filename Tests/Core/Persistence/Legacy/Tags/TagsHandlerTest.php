<?php

namespace Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Tags;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;
use Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Content\LanguageHandlerMock;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;

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

    protected function getTagsHandler(array $mockedMethods = array('updateSubtreeModificationTime'))
    {
        return $this->getMock(
            'Netgen\\TagsBundle\\Core\\Persistence\\Legacy\\Tags\\Handler',
            $mockedMethods,
            array(
                $this->gateway = $this->getMock('Netgen\\TagsBundle\\Core\\Persistence\\Legacy\\Tags\\Gateway'),
                $this->mapper = $this->getMock(
                    'Netgen\\TagsBundle\\Core\\Persistence\\Legacy\\Tags\\Mapper',
                    array(),
                    array(
                        new LanguageHandlerMock(),
                        new MaskGenerator(
                            new LanguageHandlerMock()
                        ),
                    )
                ),
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::__construct
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::load
     */
    public function testLoad()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getFullTagData')
            ->with(42)
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'eztags_id' => 42,
                        ),
                    )
                )
            );

        $this->mapper
            ->expects($this->once())
            ->method('extractTagListFromRows')
            ->with(array(array('eztags_id' => 42)))
            ->will($this->returnValue(array(new Tag(array('id' => 42)))));

        $tag = $handler->load(42);

        $this->assertInstanceOf(
            'Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag',
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
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getFullTagData')
            ->with(42)
            ->will($this->returnValue(array()));

        $this->mapper
            ->expects($this->never())
            ->method('extractTagListFromRows');

        $handler->load(42);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadTagInfo
     */
    public function testLoadTagInfo()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getBasicTagData')
            ->with(42)
            ->will(
                $this->returnValue(
                    array(
                        'id' => 42,
                    )
                )
            );

        $this->mapper
            ->expects($this->once())
            ->method('createTagInfoFromRow')
            ->with(array('id' => 42))
            ->will($this->returnValue(new TagInfo(array('id' => 42))));

        $tagInfo = $handler->loadTagInfo(42);

        $this->assertInstanceOf(
            'Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\TagInfo',
            $tagInfo
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadByRemoteId
     */
    public function testLoadByRemoteId()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getFullTagDataByRemoteId')
            ->with('abcdef')
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'eztags_remote_id' => 'abcdef',
                        ),
                    )
                )
            );

        $this->mapper
            ->expects($this->once())
            ->method('extractTagListFromRows')
            ->with(array(array('eztags_remote_id' => 'abcdef')))
            ->will($this->returnValue(array(new Tag(array('remoteId' => 'abcdef')))));

        $tag = $handler->loadByRemoteId('abcdef');

        $this->assertInstanceOf(
            'Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag',
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadByRemoteId
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadByRemoteIdThrowsNotFoundException()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getFullTagDataByRemoteId')
            ->with('abcdef')
            ->will($this->returnValue(array()));

        $this->mapper
            ->expects($this->never())
            ->method('extractTagListFromRows');

        $handler->loadByRemoteId('abcdef');
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadTagInfoByRemoteId
     */
    public function testLoadTagInfoByRemoteId()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getBasicTagDataByRemoteId')
            ->with('12345')
            ->will(
                $this->returnValue(
                    array(
                        'remote_id' => '12345',
                    )
                )
            );

        $this->mapper
            ->expects($this->once())
            ->method('createTagInfoFromRow')
            ->with(array('remote_id' => '12345'))
            ->will($this->returnValue(new TagInfo(array('remoteId' => '12345'))));

        $tagInfo = $handler->loadTagInfoByRemoteId('12345');

        $this->assertInstanceOf(
            'Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\TagInfo',
            $tagInfo
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadTagByKeywordAndParentId
     */
    public function testLoadTagByKeywordAndParentId()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getFullTagDataByKeywordAndParentId')
            ->with('eztags', 42)
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'eztags_id' => 42,
                            'eztags_keyword' => 'eztags',
                            'eztags_keyword_keyword' => 'eztags',
                        ),
                    )
                )
            );

        $this->mapper
            ->expects($this->once())
            ->method('extractTagListFromRows')
            ->with(array(array('eztags_id' => 42, 'eztags_keyword' => 'eztags', 'eztags_keyword_keyword' => 'eztags')))
            ->will($this->returnValue(array(new Tag(array('id' => 42, 'keywords' => array('eng-GB' => 'eztags'))))));

        $tag = $handler->loadTagByKeywordAndParentId('eztags', 42);

        $this->assertInstanceOf(
            'Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag',
            $tag
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadTagByKeywordAndParentId
     */
    public function testLoadTagByKeywordAndParentIdThrowsNotFoundException()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getFullTagDataByKeywordAndParentId')
            ->with('unknown', 999);

        $handler->loadTagByKeywordAndParentId('unknown', 999);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadChildren
     */
    public function testLoadChildren()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getChildren')
            ->with(42)
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'eztags_id' => 43,
                        ),
                        array(
                            'eztags_id' => 44,
                        ),
                        array(
                            'eztags_id' => 45,
                        ),
                    )
                )
            );

        $this->mapper
            ->expects($this->once())
            ->method('extractTagListFromRows')
            ->with(
                array(
                    array('eztags_id' => 43),
                    array('eztags_id' => 44),
                    array('eztags_id' => 45),
                )
            )
            ->will(
                $this->returnValue(
                    array(
                        new Tag(array('id' => 43)),
                        new Tag(array('id' => 44)),
                        new Tag(array('id' => 45)),
                    )
                )
            );

        $tags = $handler->loadChildren(42);

        $this->assertCount(3, $tags);

        foreach ($tags as $tag) {
            $this->assertInstanceOf(
                'Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag',
                $tag
            );
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::getChildrenCount
     */
    public function testGetChildrenCount()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getChildrenCount')
            ->with(42)
            ->will($this->returnValue(3));

        $tagsCount = $handler->getChildrenCount(42);

        $this->assertEquals(3, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadTagsByKeyword
     */
    public function testLoadTagsByKeyword()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getTagsByKeyword')
            ->with('eztags', 'eng-GB')
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'eztags_keyword' => 'eztags',
                            'eztags_main_language_id' => 4,
                        ),
                        array(
                            'eztags_keyword' => 'eztags',
                            'eztags_main_language_id' => 4,
                        ),
                    )
                )
            );

        $this->mapper
            ->expects($this->once())
            ->method('extractTagListFromRows')
            ->with(
                array(
                    array('eztags_keyword' => 'eztags', 'eztags_main_language_id' => 4),
                    array('eztags_keyword' => 'eztags', 'eztags_main_language_id' => 4),
                )
            )
            ->will(
                $this->returnValue(
                    array(
                        new Tag(array('keywords' => array('eng-GB' => 'eztags'))),
                        new Tag(array('keywords' => array('eng-GB' => 'eztags'))),
                    )
                )
            );

        $tags = $handler->loadTagsByKeyword('eztags', 'eng-GB');

        $this->assertCount(2, $tags);

        foreach ($tags as $tag) {
            $this->assertInstanceOf(
                'Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag',
                $tag
            );
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::getTagsByKeywordCount
     */
    public function testGetTagsByKeywordCount()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getTagsByKeywordCount')
            ->with('eztags', 'eng-GB')
            ->will($this->returnValue(2));

        $tagsCount = $handler->getTagsByKeywordCount('eztags', 'eng-GB');

        $this->assertEquals(2, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadSynonyms
     */
    public function testLoadSynonyms()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getSynonyms')
            ->with(42)
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'eztags_id' => 43,
                        ),
                        array(
                            'eztags_id' => 44,
                        ),
                        array(
                            'eztags_id' => 45,
                        ),
                    )
                )
            );

        $this->mapper
            ->expects($this->once())
            ->method('extractTagListFromRows')
            ->with(
                array(
                    array('eztags_id' => 43),
                    array('eztags_id' => 44),
                    array('eztags_id' => 45),
                )
            )
            ->will(
                $this->returnValue(
                    array(
                        new Tag(array('id' => 43)),
                        new Tag(array('id' => 44)),
                        new Tag(array('id' => 45)),
                    )
                )
            );

        $tags = $handler->loadSynonyms(42);

        $this->assertCount(3, $tags);

        foreach ($tags as $tag) {
            $this->assertInstanceOf(
                'Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag',
                $tag
            );
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::getSynonymCount
     */
    public function testGetSynonymCount()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getSynonymCount')
            ->with(42)
            ->will($this->returnValue(3));

        $tagsCount = $handler->getSynonymCount(42);

        $this->assertEquals(3, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadRelatedContentIds
     */
    public function testLoadRelatedContentIds()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getRelatedContentIds')
            ->with(42)
            ->will($this->returnValue(array(43, 44, 45)));

        $contentIds = $handler->loadRelatedContentIds(42);

        $this->assertEquals(array(43, 44, 45), $contentIds);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::getRelatedContentCount
     */
    public function testGetRelatedContentCount()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects($this->once())
            ->method('getRelatedContentCount')
            ->with(42)
            ->will($this->returnValue(3));

        $tagsCount = $handler->getRelatedContentCount(42);

        $this->assertEquals(3, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::create
     */
    public function testCreate()
    {
        $handler = $this->getTagsHandler(array('load', 'updateSubtreeModificationTime'));

        $this->gateway
            ->expects($this->once())
            ->method('getBasicTagData')
            ->with(21)
            ->will(
                $this->returnValue(
                    array(
                        'id' => 21,
                        'depth' => 2,
                        'path_string' => '/1/2/',
                    )
                )
            );

        $this->gateway
            ->expects($this->once())
            ->method('create')
            ->with(
                new CreateStruct(
                    array(
                        'parentTagId' => 21,
                        'mainLanguageCode' => 'eng-GB',
                        'keywords' => array('eng-GB' => 'New tag'),
                        'remoteId' => '123456abcdef',
                        'alwaysAvailable' => true,
                    )
                ),
                array(
                    'id' => 21,
                    'depth' => 2,
                    'path_string' => '/1/2/',
                )
            )
            ->will(
                $this->returnValue(
                    95
                )
            );

        $handler->expects($this->once())
            ->method('load')
            ->with(95)
            ->will(
                $this->returnValue(
                    new Tag(
                        array(
                            'id' => 95,
                            'parentTagId' => 21,
                            'mainTagId' => 0,
                            'keywords' => array('eng-GB' => 'New tag'),
                            'depth' => 3,
                            'pathString' => '/1/2/95/',
                            'remoteId' => '123456abcdef',
                            'alwaysAvailable' => true,
                            'mainLanguageCode' => 'eng-GB',
                            'languageIds' => array(4),
                        )
                    )
                )
            );

        $tag = $handler->create(
            new CreateStruct(
                array(
                    'parentTagId' => 21,
                    'mainLanguageCode' => 'eng-GB',
                    'keywords' => array('eng-GB' => 'New tag'),
                    'remoteId' => '123456abcdef',
                    'alwaysAvailable' => true,
                )
            )
        );

        $this->assertInstanceOf(
            'Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag',
            $tag
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 95,
                'parentTagId' => 21,
                'keywords' => array('eng-GB' => 'New tag'),
                'remoteId' => '123456abcdef',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
                'languageIds' => array(4),
            ),
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::create
     */
    public function testCreateWithNoParent()
    {
        $handler = $this->getTagsHandler(array('load', 'updateSubtreeModificationTime'));

        $this->gateway
            ->expects($this->once())
            ->method('create')
            ->with(
                new CreateStruct(
                    array(
                        'parentTagId' => 0,
                        'mainLanguageCode' => 'eng-GB',
                        'keywords' => array('eng-GB' => 'New tag'),
                        'remoteId' => '123456abcdef',
                        'alwaysAvailable' => true,
                    )
                )
            )
            ->will(
                $this->returnValue(
                    95
                )
            );

        $handler->expects($this->once())
            ->method('load')
            ->with(95)
            ->will(
                $this->returnValue(
                    new Tag(
                        array(
                            'id' => 95,
                            'parentTagId' => 0,
                            'mainTagId' => 0,
                            'keywords' => array('eng-GB' => 'New tag'),
                            'depth' => 3,
                            'pathString' => '/1/2/95/',
                            'remoteId' => '123456abcdef',
                            'alwaysAvailable' => true,
                            'mainLanguageCode' => 'eng-GB',
                            'languageIds' => array(4),
                        )
                    )
                )
            );

        $tag = $handler->create(
            new CreateStruct(
                array(
                    'parentTagId' => 0,
                    'mainLanguageCode' => 'eng-GB',
                    'keywords' => array('eng-GB' => 'New tag'),
                    'remoteId' => '123456abcdef',
                    'alwaysAvailable' => true,
                )
            )
        );

        $this->assertInstanceOf(
            'Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag',
            $tag
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 95,
                'parentTagId' => 0,
                'keywords' => array('eng-GB' => 'New tag'),
                'remoteId' => '123456abcdef',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
                'languageIds' => array(4),
            ),
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::update
     */
    public function testUpdate()
    {
        $handler = $this->getTagsHandler(array('load', 'updateSubtreeModificationTime'));

        $this->gateway
            ->expects($this->once())
            ->method('update')
            ->with(
                new UpdateStruct(
                    array(
                        'keywords' => array('eng-US' => 'Updated tag US', 'eng-GB' => 'Updated tag'),
                        'remoteId' => '123456abcdef',
                        'mainLanguageCode' => 'eng-US',
                        'alwaysAvailable' => true,
                    )
                ),
                40
            );

        $handler
            ->expects($this->once())
            ->method('load')
            ->with(40)
            ->will(
                $this->returnValue(
                    new Tag(
                        array(
                            'id' => 40,
                            'keywords' => array('eng-US' => 'Updated tag US', 'eng-GB' => 'Updated tag'),
                            'remoteId' => '123456abcdef',
                            'mainLanguageCode' => 'eng-US',
                            'alwaysAvailable' => true,
                            'languageIds' => array(2, 4),
                        )
                    )
                )
            );

        $tag = $handler->update(
            new UpdateStruct(
                array(
                    'keywords' => array('eng-US' => 'Updated tag US', 'eng-GB' => 'Updated tag'),
                    'remoteId' => '123456abcdef',
                    'mainLanguageCode' => 'eng-US',
                    'alwaysAvailable' => true,
                )
            ),
            40
        );

        $this->assertInstanceOf(
            'Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag',
            $tag
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 40,
                'keywords' => array('eng-US' => 'Updated tag US', 'eng-GB' => 'Updated tag'),
                'remoteId' => '123456abcdef',
                'mainLanguageCode' => 'eng-US',
                'alwaysAvailable' => true,
                'languageIds' => array(2, 4),
            ),
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::addSynonym
     */
    public function testAddSynonym()
    {
        $handler = $this->getTagsHandler(array('load', 'updateSubtreeModificationTime'));

        $this->gateway
            ->expects($this->once())
            ->method('getBasicTagData')
            ->with(21)
            ->will(
                $this->returnValue(
                    array(
                        'id' => 21,
                        'parent_id' => 1,
                        'depth' => 2,
                        'path_string' => '/1/21/',
                    )
                )
            );

        $this->gateway
            ->expects($this->once())
            ->method('createSynonym')
            ->with(
                new SynonymCreateStruct(
                    array(
                        'mainTagId' => 21,
                        'mainLanguageCode' => 'eng-GB',
                        'keywords' => array('eng-GB' => 'New synonym'),
                        'remoteId' => '12345',
                        'alwaysAvailable' => true,
                    )
                ),
                array(
                    'id' => 21,
                    'parent_id' => 1,
                    'depth' => 2,
                    'path_string' => '/1/21/',
                )
            )
            ->will(
                $this->returnValue(
                    95
                )
            );

        $handler
            ->expects($this->once())
            ->method('load')
            ->with(95)
            ->will(
                $this->returnValue(
                    new Tag(
                        array(
                            'id' => 95,
                            'parentTagId' => 1,
                            'mainTagId' => 21,
                            'keywords' => array('eng-GB' => 'New synonym'),
                            'depth' => 2,
                            'pathString' => '/1/95/',
                            'remoteId' => '12345',
                            'mainLanguageCode' => 'eng-GB',
                            'alwaysAvailable' => true,
                            'languageIds' => array(4),
                        )
                    )
                )
            );

        $tag = $handler->addSynonym(
            new SynonymCreateStruct(
                array(
                    'mainTagId' => 21,
                    'mainLanguageCode' => 'eng-GB',
                    'keywords' => array('eng-GB' => 'New synonym'),
                    'remoteId' => '12345',
                    'alwaysAvailable' => true,
                )
            )
        );

        $this->assertInstanceOf(
            'Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag',
            $tag
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 95,
                'parentTagId' => 1,
                'mainTagId' => 21,
                'keywords' => array('eng-GB' => 'New synonym'),
                'depth' => 2,
                'pathString' => '/1/95/',
                'remoteId' => '12345',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
                'languageIds' => array(4),
            ),
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::convertToSynonym
     */
    public function testConvertToSynonym()
    {
        $handler = $this->getTagsHandler(array('load', 'loadTagInfo', 'loadSynonyms', 'updateSubtreeModificationTime'));

        $tag = new TagInfo(
            array(
                'id' => 16,
                'parentTagId' => 0,
            )
        );

        $mainTagData = array(
            'id' => 66,
        );

        $synonyms = array(
            new Tag(array('id' => 95)),
            new Tag(array('id' => 96)),
        );

        $handler
            ->expects($this->at(0))
            ->method('loadTagInfo')
            ->with(16)
            ->will(
                $this->returnValue(
                    $tag
                )
            );

        $this->gateway
            ->expects($this->at(0))
            ->method('getBasicTagData')
            ->with(66)
            ->will($this->returnValue($mainTagData));

        $handler
            ->expects($this->at(1))
            ->method('loadSynonyms')
            ->with(16)
            ->will($this->returnValue($synonyms));

        foreach ($synonyms as $index => $synonym) {
            $this->gateway
                ->expects($this->at($index + 1))
                ->method('moveSynonym')
                ->with($synonym->id, $mainTagData);
        }

        $this->gateway
            ->expects($this->once())
            ->method('convertToSynonym')
            ->with(16, $mainTagData);

        $handler
            ->expects($this->at(4))
            ->method('load')
            ->with(16)
            ->will(
                $this->returnValue(
                    new Tag(
                        array(
                            'id' => 16,
                        )
                    )
                )
            );

        $synonym = $handler->convertToSynonym(16, 66);

        $this->assertInstanceOf(
            'Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag',
            $synonym
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 16,
            ),
            $synonym
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::merge
     */
    public function testMerge()
    {
        $handler = $this->getTagsHandler(array('loadTagInfo', 'loadSynonyms', 'updateSubtreeModificationTime'));

        $handler
            ->expects($this->at(0))
            ->method('loadTagInfo')
            ->with(40)
            ->will(
                $this->returnValue(
                    new TagInfo(
                        array(
                            'id' => 40,
                            'parentTagId' => 7,
                        )
                    )
                )
            );

        $handler
            ->expects($this->at(1))
            ->method('loadTagInfo')
            ->with(42)
            ->will(
                $this->returnValue(
                    new TagInfo(
                        array(
                            'id' => 42,
                        )
                    )
                )
            );

        $tags = array(
            new Tag(array('id' => 50)),
            new Tag(array('id' => 51)),
        );

        $handler
            ->expects($this->once())
            ->method('loadSynonyms')
            ->with(40)
            ->will(
                $this->returnValue($tags)
            );

        array_push($tags, new Tag(array('id' => 40)));

        foreach ($tags as $index => $tag) {
            $this->gateway
                ->expects($this->at($index * 2))
                ->method('transferTagAttributeLinks')
                ->with($tag->id, 42);

            $this->gateway
                ->expects($this->at($index * 2 + 1))
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
        $this->markTestIncomplete('@TODO: Implement test for copySubtree');
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::moveSubtree
     */
    public function testMoveSubtree()
    {
        $handler = $this->getTagsHandler(array('load', 'updateSubtreeModificationTime'));

        $sourceData = array(
            'id' => 42,
            'parent_id' => 21,
            'depth' => 3,
            'path_string' => '/1/21/42/',
        );

        $destinationData = array(
            'id' => 66,
            'parent_id' => 21,
            'path_string' => '/1/21/66/',
        );

        $movedData = array(
            'id' => 42,
            'parent_id' => 66,
            'depth' => 4,
            'path_string' => '/1/21/66/42/',
            'modified' => 12345,
        );

        $this->gateway
            ->expects($this->at(0))
            ->method('getBasicTagData')
            ->with(42)
            ->will($this->returnValue($sourceData));

        $this->gateway
            ->expects($this->at(1))
            ->method('getBasicTagData')
            ->with(66)
            ->will($this->returnValue($destinationData));

        $this->gateway
            ->expects($this->once())
            ->method('moveSubtree')
            ->with($sourceData, $destinationData)
            ->will($this->returnValue($movedData));

        $handler
            ->expects($this->once())
            ->method('load')
            ->with($movedData['id'])
            ->will(
                $this->returnValue(
                    new Tag(
                        array(
                            'id' => $movedData['id'],
                            'parentTagId' => $movedData['parent_id'],
                            'depth' => $movedData['depth'],
                            'pathString' => $movedData['path_string'],
                            'modificationDate' => $movedData['modified'],
                        )
                    )
                )
            );

        $movedTag = $handler->moveSubtree(42, 66);

        $this->assertInstanceOf(
            'Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag',
            $movedTag
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => $movedData['id'],
                'parentTagId' => $movedData['parent_id'],
                'depth' => $movedData['depth'],
                'pathString' => $movedData['path_string'],
                'modificationDate' => $movedData['modified'],
            ),
            $movedTag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler::deleteTag
     */
    public function testDeleteTag()
    {
        $handler = $this->getTagsHandler(array('loadTagInfo', 'updateSubtreeModificationTime'));

        $handler
            ->expects($this->once())
            ->method('loadTagInfo')
            ->with(40)
            ->will(
                $this->returnValue(
                    new TagInfo(
                        array(
                            'id' => 40,
                            'parentTagId' => 21,
                        )
                    )
                )
            );

        $this->gateway
            ->expects($this->once())
            ->method('deleteTag')
            ->with(40);

        $handler->deleteTag(40);
    }
}
