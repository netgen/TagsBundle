<?php

namespace Netgen\TagsBundle\Tests\Core\SignalSlot;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;
use Netgen\TagsBundle\Core\Repository\TagsService as CoreTagsService;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\AddSynonymSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\ConvertToSynonymSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\CopySubtreeSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\CreateTagSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\DeleteTagSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\MergeTagsSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\MoveSubtreeSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\UpdateTagSignal;
use Netgen\TagsBundle\Core\SignalSlot\TagsService;
use PHPUnit\Framework\TestCase;

class TagsServiceTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tagsService;

    /**
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $signalDispatcher;

    /**
     * Sets up the test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->tagsService = $this->createMock(CoreTagsService::class);

        $this->signalDispatcher = $this->createMock(SignalDispatcher::class);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::__construct
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::loadTag
     */
    public function testLoadTag()
    {
        $this->tagsService
            ->expects(self::once())
            ->method('loadTag')
            ->with(self::equalTo(42))
            ->will(
                self::returnValue(
                    new Tag(['id' => 42])
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $tag = $signalSlotService->loadTag(42);

        self::assertInstanceOf(Tag::class, $tag);
        self::assertEquals(42, $tag->id);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::loadTagByRemoteId
     */
    public function testLoadTagByRemoteId()
    {
        $this->tagsService
            ->expects(self::once())
            ->method('loadTagByRemoteId')
            ->with(self::equalTo('12345'))
            ->will(
                self::returnValue(
                    new Tag(['remoteId' => '12345'])
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $tag = $signalSlotService->loadTagByRemoteId('12345');

        self::assertInstanceOf(Tag::class, $tag);
        self::assertEquals('12345', $tag->remoteId);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::loadTagByUrl
     */
    public function testLoadTagByUrl()
    {
        $this->tagsService
            ->expects(self::once())
            ->method('loadTagByUrl')
            ->with('Netgen/TagsBundle', ['eng-GB'])
            ->will(
                self::returnValue(
                    new Tag(['keywords' => ['eng-GB' => 'TagsBundle']])
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $tag = $signalSlotService->loadTagByUrl('Netgen/TagsBundle', ['eng-GB']);

        self::assertInstanceOf(Tag::class, $tag);
        self::assertEquals(['eng-GB' => 'TagsBundle'], $tag->keywords);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::loadTagChildren
     */
    public function testLoadTagChildren()
    {
        $this->tagsService
            ->expects(self::once())
            ->method('loadTagChildren')
            ->with(self::equalTo(new Tag(['id' => 42])))
            ->will(
                self::returnValue(
                    [
                        new Tag(['parentTagId' => 42]),
                        new Tag(['parentTagId' => 42]),
                    ]
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $tags = $signalSlotService->loadTagChildren(new Tag(['id' => 42]));

        self::assertCount(2, $tags);

        foreach ($tags as $tag) {
            self::assertInstanceOf(Tag::class, $tag);
            self::assertEquals(42, $tag->parentTagId);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::getTagChildrenCount
     */
    public function testGetTagChildrenCount()
    {
        $this->tagsService
            ->expects(self::once())
            ->method('getTagChildrenCount')
            ->with(self::equalTo(new Tag(['id' => 42])))
            ->will(self::returnValue(2));

        $signalSlotService = $this->getSignalSlotService();
        $tagsCount = $signalSlotService->getTagChildrenCount(new Tag(['id' => 42]));

        self::assertEquals(2, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::loadTagsByKeyword
     */
    public function testLoadTagsByKeyword()
    {
        $this->tagsService
            ->expects(self::once())
            ->method('loadTagsByKeyword')
            ->with('netgen', 'eng-GB')
            ->will(
                self::returnValue(
                    [
                        new Tag(['keywords' => ['eng-GB' => 'netgen']]),
                        new Tag(['keywords' => ['eng-GB' => 'netgen']]),
                    ]
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $tags = $signalSlotService->loadTagsByKeyword('netgen', 'eng-GB');

        self::assertCount(2, $tags);

        foreach ($tags as $tag) {
            self::assertInstanceOf(Tag::class, $tag);
            self::assertEquals(['eng-GB' => 'netgen'], $tag->keywords);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::getTagsByKeywordCount
     */
    public function testGetTagsByKeywordCount()
    {
        $this->tagsService
            ->expects(self::once())
            ->method('getTagsByKeywordCount')
            ->with('netgen', 'eng-GB')
            ->will(self::returnValue(2));

        $signalSlotService = $this->getSignalSlotService();
        $tagsCount = $signalSlotService->getTagsByKeywordCount('netgen', 'eng-GB');

        self::assertEquals(2, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::loadTagSynonyms
     */
    public function testLoadTagSynonyms()
    {
        $this->tagsService
            ->expects(self::once())
            ->method('loadTagSynonyms')
            ->with(self::equalTo(new Tag(['id' => 42])))
            ->will(
                self::returnValue(
                    [
                        new Tag(['mainTagId' => 42]),
                        new Tag(['mainTagId' => 42]),
                    ]
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $tags = $signalSlotService->loadTagSynonyms(new Tag(['id' => 42]));

        self::assertCount(2, $tags);

        foreach ($tags as $tag) {
            self::assertInstanceOf(Tag::class, $tag);
            self::assertEquals(42, $tag->mainTagId);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::getTagSynonymCount
     */
    public function testGetTagSynonymCount()
    {
        $this->tagsService
            ->expects(self::once())
            ->method('getTagSynonymCount')
            ->with(self::equalTo(new Tag(['id' => 42])))
            ->will(self::returnValue(2));

        $signalSlotService = $this->getSignalSlotService();
        $tagsCount = $signalSlotService->getTagSynonymCount(new Tag(['id' => 42]));

        self::assertEquals(2, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::getRelatedContent
     */
    public function testGetRelatedContent()
    {
        $this->tagsService
            ->expects(self::once())
            ->method('getRelatedContent')
            ->with(self::equalTo(new Tag(['id' => 42])))
            ->will(
                self::returnValue(
                    [
                        new Content(),
                        new Content(),
                    ]
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $content = $signalSlotService->getRelatedContent(new Tag(['id' => 42]));

        self::assertCount(2, $content);

        foreach ($content as $contentItem) {
            self::assertInstanceOf(APIContent::class, $contentItem);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::getRelatedContentCount
     */
    public function testGetRelatedContentCount()
    {
        $this->tagsService
            ->expects(self::once())
            ->method('getRelatedContentCount')
            ->with(self::equalTo(new Tag(['id' => 42])))
            ->will(self::returnValue(2));

        $signalSlotService = $this->getSignalSlotService();
        $contentCount = $signalSlotService->getRelatedContentCount(new Tag(['id' => 42]));

        self::assertEquals(2, $contentCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::createTag
     */
    public function testCreateTag()
    {
        $tagCreateStruct = new TagCreateStruct();
        $tagCreateStruct->parentTagId = '42';
        $tagCreateStruct->mainLanguageCode = 'eng-GB';
        $tagCreateStruct->alwaysAvailable = true;
        $tagCreateStruct->setKeyword('netgen');

        $this->tagsService
            ->expects(self::once())
            ->method('createTag')
            ->with(self::equalTo($tagCreateStruct))
            ->will(
                self::returnValue(
                    new Tag(
                        [
                            'id' => 24,
                            'parentTagId' => 42,
                            'keywords' => ['eng-GB' => 'netgen'],
                            'mainLanguageCode' => 'eng-GB',
                            'alwaysAvailable' => true,
                        ]
                    )
                )
            );

        $this->signalDispatcher
            ->expects(self::once())
            ->method('emit')
            ->with(
                self::equalTo(
                    new CreateTagSignal(
                        [
                            'tagId' => 24,
                            'parentTagId' => 42,
                            'keywords' => ['eng-GB' => 'netgen'],
                            'mainLanguageCode' => 'eng-GB',
                            'alwaysAvailable' => true,
                        ]
                    )
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $createdTag = $signalSlotService->createTag($tagCreateStruct);

        self::assertInstanceOf(Tag::class, $createdTag);

        self::assertEquals(24, $createdTag->id);
        self::assertEquals(42, $createdTag->parentTagId);
        self::assertEquals(['eng-GB' => 'netgen'], $createdTag->keywords);
        self::assertEquals('eng-GB', $createdTag->mainLanguageCode);
        self::assertTrue($createdTag->alwaysAvailable);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::updateTag
     */
    public function testUpdateTag()
    {
        $tagUpdateStruct = new TagUpdateStruct();
        $tagUpdateStruct->alwaysAvailable = true;
        $tagUpdateStruct->setKeyword('netgen');

        $tag = new Tag(
            [
                'id' => 42,
                'keywords' => ['eng-GB' => 'ez'],
                'remoteId' => '123456',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => false,
            ]
        );

        $this->tagsService
            ->expects(self::once())
            ->method('updateTag')
            ->with(
                self::equalTo($tag),
                self::equalTo($tagUpdateStruct)
            )
            ->will(
                self::returnValue(
                    new Tag(
                        [
                            'id' => 42,
                            'keywords' => ['eng-GB' => 'netgen'],
                            'remoteId' => 123456,
                            'mainLanguageCode' => 'eng-GB',
                            'alwaysAvailable' => true,
                        ]
                    )
                )
            );

        $this->signalDispatcher
            ->expects(self::once())
            ->method('emit')
            ->with(
                self::equalTo(
                    new UpdateTagSignal(
                        [
                            'tagId' => 42,
                            'keywords' => ['eng-GB' => 'netgen'],
                            'remoteId' => '123456',
                            'mainLanguageCode' => 'eng-GB',
                            'alwaysAvailable' => true,
                        ]
                    )
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $updatedTag = $signalSlotService->updateTag($tag, $tagUpdateStruct);

        self::assertInstanceOf(Tag::class, $updatedTag);

        self::assertEquals(42, $updatedTag->id);
        self::assertEquals(['eng-GB' => 'netgen'], $updatedTag->keywords);
        self::assertEquals('123456', $updatedTag->remoteId);
        self::assertEquals('eng-GB', $updatedTag->mainLanguageCode);
        self::assertTrue($updatedTag->alwaysAvailable);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::addSynonym
     */
    public function testAddSynonym()
    {
        $synonymCreateStruct = new SynonymCreateStruct();
        $synonymCreateStruct->mainTagId = '42';
        $synonymCreateStruct->mainLanguageCode = 'eng-GB';
        $synonymCreateStruct->alwaysAvailable = true;
        $synonymCreateStruct->setKeyword('netgenlabs');

        $this->tagsService
            ->expects(self::once())
            ->method('addSynonym')
            ->with(
                self::equalTo(
                    $synonymCreateStruct
                )
            )
            ->will(
                self::returnValue(
                    new Tag(
                        [
                            'id' => 24,
                            'keywords' => ['eng-GB' => 'netgenlabs'],
                            'mainTagId' => 42,
                            'mainLanguageCode' => 'eng-GB',
                            'alwaysAvailable' => true,
                        ]
                    )
                )
            );

        $this->signalDispatcher
            ->expects(self::once())
            ->method('emit')
            ->with(
                self::equalTo(
                    new AddSynonymSignal(
                        [
                            'tagId' => 24,
                            'mainTagId' => 42,
                            'keywords' => ['eng-GB' => 'netgenlabs'],
                            'mainLanguageCode' => 'eng-GB',
                            'alwaysAvailable' => true,
                        ]
                    )
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $synonym = $signalSlotService->addSynonym($synonymCreateStruct);

        self::assertInstanceOf(Tag::class, $synonym);

        self::assertEquals(24, $synonym->id);
        self::assertEquals(42, $synonym->mainTagId);
        self::assertEquals(['eng-GB' => 'netgenlabs'], $synonym->keywords);
        self::assertEquals('eng-GB', $synonym->mainLanguageCode);
        self::assertTrue($synonym->alwaysAvailable);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::convertToSynonym
     */
    public function testConvertToSynonym()
    {
        $tag = new Tag(
            [
                'id' => 42,
            ]
        );

        $mainTag = new Tag(
            [
                'id' => 24,
            ]
        );

        $this->tagsService
            ->expects(self::once())
            ->method('convertToSynonym')
            ->with(
                self::equalTo($tag),
                self::equalTo($mainTag)
            )
            ->will(
                self::returnValue(
                    new Tag(
                        [
                            'id' => 42,
                            'mainTagId' => 24,
                        ]
                    )
                )
            );

        $this->signalDispatcher
            ->expects(self::once())
            ->method('emit')
            ->with(
                self::equalTo(
                    new ConvertToSynonymSignal(
                        [
                            'tagId' => 42,
                            'mainTagId' => 24,
                        ]
                    )
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $synonym = $signalSlotService->convertToSynonym($tag, $mainTag);

        self::assertInstanceOf(Tag::class, $synonym);

        self::assertEquals(42, $synonym->id);
        self::assertEquals(24, $synonym->mainTagId);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::mergeTags
     */
    public function testMergeTags()
    {
        $tag = new Tag(
            [
                'id' => 42,
            ]
        );

        $targetTag = new Tag(
            [
                'id' => 24,
            ]
        );

        $this->tagsService
            ->expects(self::once())
            ->method('mergeTags')
            ->with(
                self::equalTo($tag),
                self::equalTo($targetTag)
            );

        $this->signalDispatcher
            ->expects(self::once())
            ->method('emit')
            ->with(
                self::equalTo(
                    new MergeTagsSignal(
                        [
                            'tagId' => 42,
                            'targetTagId' => 24,
                        ]
                    )
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $signalSlotService->mergeTags($tag, $targetTag);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::copySubtree
     */
    public function testCopySubtree()
    {
        $tag = new Tag(
            [
                'id' => 24,
                'keywords' => ['eng-GB' => 'netgen'],
            ]
        );

        $targetTag = new Tag(
            [
                'id' => 25,
            ]
        );

        $this->tagsService
            ->expects(self::once())
            ->method('copySubtree')
            ->with(
                self::equalTo($tag),
                self::equalTo($targetTag)
            )
            ->will(
                self::returnValue(
                    new Tag(
                        [
                            'id' => 42,
                            'parentTagId' => 25,
                            'keywords' => ['eng-GB' => 'netgen'],
                        ]
                    )
                )
            );

        $this->signalDispatcher
            ->expects(self::once())
            ->method('emit')
            ->with(
                self::equalTo(
                    new CopySubtreeSignal(
                        [
                            'sourceTagId' => 24,
                            'targetParentTagId' => 25,
                            'newTagId' => 42,
                        ]
                    )
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $copiedTag = $signalSlotService->copySubtree($tag, $targetTag);

        self::assertInstanceOf(Tag::class, $copiedTag);

        self::assertEquals(42, $copiedTag->id);
        self::assertEquals(25, $copiedTag->parentTagId);
        self::assertEquals(['eng-GB' => 'netgen'], $copiedTag->keywords);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::moveSubtree
     */
    public function testMoveSubtree()
    {
        $tag = new Tag(
            [
                'id' => 24,
            ]
        );

        $targetTag = new Tag(
            [
                'id' => 25,
            ]
        );

        $this->tagsService
            ->expects(self::once())
            ->method('moveSubtree')
            ->with(
                self::equalTo($tag),
                self::equalTo($targetTag)
            )
            ->will(
                self::returnValue(
                    new Tag(
                        [
                            'id' => 24,
                            'parentTagId' => 25,
                        ]
                    )
                )
            );

        $this->signalDispatcher
            ->expects(self::once())
            ->method('emit')
            ->with(
                self::equalTo(
                    new MoveSubtreeSignal(
                        [
                            'sourceTagId' => 24,
                            'targetParentTagId' => 25,
                        ]
                    )
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $movedTag = $signalSlotService->moveSubtree($tag, $targetTag);

        self::assertInstanceOf(Tag::class, $movedTag);

        self::assertEquals(24, $movedTag->id);
        self::assertEquals(25, $movedTag->parentTagId);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::deleteTag
     */
    public function testDeleteTag()
    {
        $tag = new Tag(
            [
                'id' => 42,
            ]
        );

        $this->tagsService
            ->expects(self::once())
            ->method('deleteTag')
            ->with(
                self::equalTo($tag)
            );

        $this->signalDispatcher
            ->expects(self::once())
            ->method('emit')
            ->with(
                self::equalTo(
                    new DeleteTagSignal(
                        [
                            'tagId' => 42,
                        ]
                    )
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $signalSlotService->deleteTag($tag);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::newTagCreateStruct
     */
    public function testNewTagCreateStruct()
    {
        $this->tagsService
            ->expects(self::once())
            ->method('newTagCreateStruct')
            ->with(self::equalTo(42), self::equalTo('eng-GB'))
            ->will(
                self::returnValue(
                    new TagCreateStruct(['parentTagId' => 42, 'mainLanguageCode' => 'eng-GB'])
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $tagCreateStruct = $signalSlotService->newTagCreateStruct(42, 'eng-GB');

        self::assertInstanceOf(TagCreateStruct::class, $tagCreateStruct);
        self::assertEquals(42, $tagCreateStruct->parentTagId);
        self::assertEquals('eng-GB', $tagCreateStruct->mainLanguageCode);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::newSynonymCreateStruct
     */
    public function testNewSynonymCreateStruct()
    {
        $this->tagsService
            ->expects(self::once())
            ->method('newSynonymCreateStruct')
            ->with(self::equalTo(42), self::equalTo('eng-GB'))
            ->will(
                self::returnValue(
                    new SynonymCreateStruct(['mainTagId' => 42, 'mainLanguageCode' => 'eng-GB'])
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $synonymCreateStruct = $signalSlotService->newSynonymCreateStruct(42, 'eng-GB');

        self::assertInstanceOf(SynonymCreateStruct::class, $synonymCreateStruct);
        self::assertEquals(42, $synonymCreateStruct->mainTagId);
        self::assertEquals('eng-GB', $synonymCreateStruct->mainLanguageCode);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::newTagUpdateStruct
     */
    public function testNewTagUpdateStruct()
    {
        $this->tagsService
            ->expects(self::once())
            ->method('newTagUpdateStruct')
            ->will(
                self::returnValue(
                    new TagUpdateStruct()
                )
            );

        $signalSlotService = $this->getSignalSlotService();
        $tagUpdateStruct = $signalSlotService->newTagUpdateStruct();

        self::assertInstanceOf(TagUpdateStruct::class, $tagUpdateStruct);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::sudo
     */
    public function testSudo()
    {
        $callback = function () {
        };

        $this->tagsService
            ->expects(self::once())
            ->method('sudo')
            ->will(self::returnValue('some_value'));

        $signalSlotService = $this->getSignalSlotService();
        $value = $signalSlotService->sudo($callback);

        self::assertEquals('some_value', $value);
    }

    /**
     * Returns signal slot service under test.
     *
     * @return \Netgen\TagsBundle\Core\SignalSlot\TagsService
     */
    protected function getSignalSlotService()
    {
        return new TagsService($this->tagsService, $this->signalDispatcher);
    }
}
