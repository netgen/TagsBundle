<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Core\Event;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\Core\Repository\Values\Content\Content;
use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagList;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;
use Netgen\TagsBundle\Core\Event\Tags;
use Netgen\TagsBundle\Core\Event\TagsService;
use Netgen\TagsBundle\Core\Repository\TagsService as CoreTagsService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class TagsServiceTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $tagsService;

    /**
     * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventDispatcher;

    protected function setUp(): void
    {
        $this->tagsService = $this->createMock(CoreTagsService::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::__construct
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::loadTag
     */
    public function testLoadTag(): void
    {
        $this->tagsService
            ->expects(self::once())
            ->method('loadTag')
            ->with(self::identicalTo(42))
            ->willReturn(
                new Tag(['id' => 42])
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $tag = $eventDispatchingService->loadTag(42);

        self::assertSame(42, $tag->id);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::loadTagByRemoteId
     */
    public function testLoadTagByRemoteId(): void
    {
        $this->tagsService
            ->expects(self::once())
            ->method('loadTagByRemoteId')
            ->with(self::identicalTo('12345'))
            ->willReturn(
                new Tag(['remoteId' => '12345'])
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $tag = $eventDispatchingService->loadTagByRemoteId('12345');

        self::assertSame('12345', $tag->remoteId);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::loadTagByUrl
     */
    public function testLoadTagByUrl(): void
    {
        $this->tagsService
            ->expects(self::once())
            ->method('loadTagByUrl')
            ->with('Netgen/TagsBundle', ['eng-GB'])
            ->willReturn(
                new Tag(['keywords' => ['eng-GB' => 'TagsBundle']])
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $tag = $eventDispatchingService->loadTagByUrl('Netgen/TagsBundle', ['eng-GB']);

        self::assertSame(['eng-GB' => 'TagsBundle'], $tag->keywords);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::loadTagChildren
     */
    public function testLoadTagChildren(): void
    {
        $tag = new Tag(['id' => 42]);

        $this->tagsService
            ->expects(self::once())
            ->method('loadTagChildren')
            ->with(self::identicalTo($tag))
            ->willReturn(
                new TagList(
                    [
                        new Tag(['parentTagId' => 42]),
                        new Tag(['parentTagId' => 42]),
                    ]
                )
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $childrenTags = $eventDispatchingService->loadTagChildren($tag);

        self::assertCount(2, $childrenTags);
        self::assertContainsOnlyInstancesOf(Tag::class, $childrenTags->toArray());

        foreach ($childrenTags->toArray() as $childTag) {
            self::assertSame(42, $childTag->parentTagId);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::getTagChildrenCount
     */
    public function testGetTagChildrenCount(): void
    {
        $tag = new Tag(['id' => 42]);

        $this->tagsService
            ->expects(self::once())
            ->method('getTagChildrenCount')
            ->with(self::identicalTo($tag))
            ->willReturn(2);

        $eventDispatchingService = $this->getEventDispatchingService();
        $tagsCount = $eventDispatchingService->getTagChildrenCount($tag);

        self::assertSame(2, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::loadTagsByKeyword
     */
    public function testLoadTagsByKeyword(): void
    {
        $this->tagsService
            ->expects(self::once())
            ->method('loadTagsByKeyword')
            ->with('netgen', 'eng-GB')
            ->willReturn(
                new TagList(
                    [
                        new Tag(['keywords' => ['eng-GB' => 'netgen']]),
                        new Tag(['keywords' => ['eng-GB' => 'netgen']]),
                    ]
                )
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $tags = $eventDispatchingService->loadTagsByKeyword('netgen', 'eng-GB');

        self::assertCount(2, $tags);
        self::assertContainsOnlyInstancesOf(Tag::class, $tags->toArray());

        foreach ($tags->toArray() as $tag) {
            self::assertSame(['eng-GB' => 'netgen'], $tag->keywords);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::getTagsByKeywordCount
     */
    public function testGetTagsByKeywordCount(): void
    {
        $this->tagsService
            ->expects(self::once())
            ->method('getTagsByKeywordCount')
            ->with('netgen', 'eng-GB')
            ->willReturn(2);

        $eventDispatchingService = $this->getEventDispatchingService();
        $tagsCount = $eventDispatchingService->getTagsByKeywordCount('netgen', 'eng-GB');

        self::assertSame(2, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::loadTagSynonyms
     */
    public function testLoadTagSynonyms(): void
    {
        $tag = new Tag(['id' => 42]);

        $this->tagsService
            ->expects(self::once())
            ->method('loadTagSynonyms')
            ->with(self::identicalTo($tag))
            ->willReturn(
                new TagList(
                    [
                        new Tag(['mainTagId' => 42]),
                        new Tag(['mainTagId' => 42]),
                    ]
                )
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $synonyms = $eventDispatchingService->loadTagSynonyms($tag);

        self::assertCount(2, $synonyms);
        self::assertContainsOnlyInstancesOf(Tag::class, $synonyms->toArray());

        foreach ($synonyms->toArray() as $synonym) {
            self::assertSame(42, $synonym->mainTagId);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::getTagSynonymCount
     */
    public function testGetTagSynonymCount(): void
    {
        $tag = new Tag(['id' => 42]);

        $this->tagsService
            ->expects(self::once())
            ->method('getTagSynonymCount')
            ->with(self::identicalTo($tag))
            ->willReturn(2);

        $eventDispatchingService = $this->getEventDispatchingService();
        $tagsCount = $eventDispatchingService->getTagSynonymCount($tag);

        self::assertSame(2, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::getRelatedContent
     */
    public function testGetRelatedContent(): void
    {
        $tag = new Tag(['id' => 42]);

        $this->tagsService
            ->expects(self::once())
            ->method('getRelatedContent')
            ->with(self::identicalTo($tag))
            ->willReturn(
                [
                    new Content(),
                    new Content(),
                ]
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $content = $eventDispatchingService->getRelatedContent($tag);

        self::assertCount(2, $content);
        self::assertContainsOnlyInstancesOf(APIContent::class, $content);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::getRelatedContentCount
     */
    public function testGetRelatedContentCount(): void
    {
        $tag = new Tag(['id' => 42]);

        $this->tagsService
            ->expects(self::once())
            ->method('getRelatedContentCount')
            ->with(self::identicalTo($tag))
            ->willReturn(2);

        $eventDispatchingService = $this->getEventDispatchingService();
        $contentCount = $eventDispatchingService->getRelatedContentCount($tag);

        self::assertSame(2, $contentCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::createTag
     */
    public function testCreateTag(): void
    {
        $tagCreateStruct = new TagCreateStruct();
        $tagCreateStruct->parentTagId = 42;
        $tagCreateStruct->mainLanguageCode = 'eng-GB';
        $tagCreateStruct->alwaysAvailable = true;
        $tagCreateStruct->setKeyword('netgen');

        $tag = new Tag(
            [
                'id' => 24,
                'parentTagId' => 42,
                'keywords' => ['eng-GB' => 'netgen'],
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
            ]
        );

        $this->tagsService
            ->expects(self::once())
            ->method('createTag')
            ->with(self::identicalTo($tagCreateStruct))
            ->willReturn($tag);

        $beforeEvent = new Tags\BeforeCreateTagEvent($tagCreateStruct);

        $this->eventDispatcher
            ->expects(self::at(0))
            ->method('dispatch')
            ->with(self::equalTo($beforeEvent))
            ->willReturn($beforeEvent);

        $this->eventDispatcher
            ->expects(self::at(1))
            ->method('dispatch')
            ->with(
                self::equalTo(new Tags\CreateTagEvent($tagCreateStruct, $tag))
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $createdTag = $eventDispatchingService->createTag($tagCreateStruct);

        self::assertSame(24, $createdTag->id);
        self::assertSame(42, $createdTag->parentTagId);
        self::assertSame(['eng-GB' => 'netgen'], $createdTag->keywords);
        self::assertSame('eng-GB', $createdTag->mainLanguageCode);
        self::assertTrue($createdTag->alwaysAvailable);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::updateTag
     */
    public function testUpdateTag(): void
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

        $updatedTag = new Tag(
            [
                'id' => 42,
                'keywords' => ['eng-GB' => 'netgen'],
                'remoteId' => '123456',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
            ]
        );

        $this->tagsService
            ->expects(self::once())
            ->method('updateTag')
            ->with(
                self::identicalTo($tag),
                self::identicalTo($tagUpdateStruct)
            )
            ->willReturn($updatedTag);

        $beforeEvent = new Tags\BeforeUpdateTagEvent($tagUpdateStruct, $tag);

        $this->eventDispatcher
            ->expects(self::at(0))
            ->method('dispatch')
            ->with(self::equalTo($beforeEvent))
            ->willReturn($beforeEvent);

        $this->eventDispatcher
            ->expects(self::at(1))
            ->method('dispatch')
            ->with(
                self::equalTo(new Tags\UpdateTagEvent($tagUpdateStruct, $updatedTag))
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $updatedTag = $eventDispatchingService->updateTag($tag, $tagUpdateStruct);

        self::assertSame(42, $updatedTag->id);
        self::assertSame(['eng-GB' => 'netgen'], $updatedTag->keywords);
        self::assertSame('123456', $updatedTag->remoteId);
        self::assertSame('eng-GB', $updatedTag->mainLanguageCode);
        self::assertTrue($updatedTag->alwaysAvailable);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::addSynonym
     */
    public function testAddSynonym(): void
    {
        $synonymCreateStruct = new SynonymCreateStruct();
        $synonymCreateStruct->mainTagId = 42;
        $synonymCreateStruct->mainLanguageCode = 'eng-GB';
        $synonymCreateStruct->alwaysAvailable = true;
        $synonymCreateStruct->setKeyword('netgenlabs');

        $synonym = new Tag(
            [
                'id' => 24,
                'keywords' => ['eng-GB' => 'netgenlabs'],
                'mainTagId' => 42,
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
            ]
        );

        $this->tagsService
            ->expects(self::once())
            ->method('addSynonym')
            ->with(self::identicalTo($synonymCreateStruct))
            ->willReturn($synonym);

        $beforeEvent = new Tags\BeforeAddSynonymEvent($synonymCreateStruct);

        $this->eventDispatcher
            ->expects(self::at(0))
            ->method('dispatch')
            ->with(self::equalTo($beforeEvent))
            ->willReturn($beforeEvent);

        $this->eventDispatcher
            ->expects(self::at(1))
            ->method('dispatch')
            ->with(
                self::equalTo(new Tags\AddSynonymEvent($synonymCreateStruct, $synonym))
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $synonym = $eventDispatchingService->addSynonym($synonymCreateStruct);

        self::assertSame(24, $synonym->id);
        self::assertSame(42, $synonym->mainTagId);
        self::assertSame(['eng-GB' => 'netgenlabs'], $synonym->keywords);
        self::assertSame('eng-GB', $synonym->mainLanguageCode);
        self::assertTrue($synonym->alwaysAvailable);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::convertToSynonym
     */
    public function testConvertToSynonym(): void
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

        $synonym = new Tag(
            [
                'id' => 42,
                'mainTagId' => 24,
            ]
        );

        $this->tagsService
            ->expects(self::once())
            ->method('convertToSynonym')
            ->with(
                self::identicalTo($tag),
                self::identicalTo($mainTag)
            )
            ->willReturn($synonym);

        $beforeEvent = new Tags\BeforeConvertToSynonymEvent($tag, $mainTag);

        $this->eventDispatcher
            ->expects(self::at(0))
            ->method('dispatch')
            ->with(self::equalTo($beforeEvent))
            ->willReturn($beforeEvent);

        $this->eventDispatcher
            ->expects(self::at(1))
            ->method('dispatch')
            ->with(
                self::equalTo(new Tags\ConvertToSynonymEvent($synonym, $mainTag))
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $synonym = $eventDispatchingService->convertToSynonym($tag, $mainTag);

        self::assertSame(42, $synonym->id);
        self::assertSame(24, $synonym->mainTagId);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::mergeTags
     */
    public function testMergeTags(): void
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
                self::identicalTo($tag),
                self::identicalTo($targetTag)
            );

        $beforeEvent = new Tags\BeforeMergeTagsEvent($tag, $targetTag);

        $this->eventDispatcher
            ->expects(self::at(0))
            ->method('dispatch')
            ->with(self::equalTo($beforeEvent))
            ->willReturn($beforeEvent);

        $this->eventDispatcher
            ->expects(self::at(1))
            ->method('dispatch')
            ->with(
                self::equalTo(new Tags\MergeTagsEvent($targetTag))
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $eventDispatchingService->mergeTags($tag, $targetTag);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::copySubtree
     */
    public function testCopySubtree(): void
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

        $copiedTag = new Tag(
            [
                'id' => 42,
                'parentTagId' => 25,
                'keywords' => ['eng-GB' => 'netgen'],
            ]
        );

        $this->tagsService
            ->expects(self::once())
            ->method('copySubtree')
            ->with(
                self::identicalTo($tag),
                self::identicalTo($targetTag)
            )
            ->willReturn($copiedTag);

        $beforeEvent = new Tags\BeforeCopySubtreeEvent($tag, $targetTag);

        $this->eventDispatcher
            ->expects(self::at(0))
            ->method('dispatch')
            ->with(self::equalTo($beforeEvent))
            ->willReturn($beforeEvent);

        $this->eventDispatcher
            ->expects(self::at(1))
            ->method('dispatch')
            ->with(
                self::equalTo(new Tags\CopySubtreeEvent($tag, $copiedTag, $targetTag))
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $copiedTag = $eventDispatchingService->copySubtree($tag, $targetTag);

        self::assertSame(42, $copiedTag->id);
        self::assertSame(25, $copiedTag->parentTagId);
        self::assertSame(['eng-GB' => 'netgen'], $copiedTag->keywords);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::moveSubtree
     */
    public function testMoveSubtree(): void
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

        $movedTag = new Tag(
            [
                'id' => 24,
                'parentTagId' => 25,
            ]
        );

        $this->tagsService
            ->expects(self::once())
            ->method('moveSubtree')
            ->with(
                self::identicalTo($tag),
                self::identicalTo($targetTag)
            )
            ->willReturn($movedTag);

        $beforeEvent = new Tags\BeforeMoveSubtreeEvent($tag, $targetTag);

        $this->eventDispatcher
            ->expects(self::at(0))
            ->method('dispatch')
            ->with(self::equalTo($beforeEvent))
            ->willReturn($beforeEvent);

        $this->eventDispatcher
            ->expects(self::at(1))
            ->method('dispatch')
            ->with(
                self::equalTo(new Tags\MoveSubtreeEvent($movedTag, $targetTag))
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $movedTag = $eventDispatchingService->moveSubtree($tag, $targetTag);

        self::assertSame(24, $movedTag->id);
        self::assertSame(25, $movedTag->parentTagId);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::deleteTag
     */
    public function testDeleteTag(): void
    {
        $tag = new Tag(
            [
                'id' => 42,
            ]
        );

        $this->tagsService
            ->expects(self::once())
            ->method('deleteTag')
            ->with(self::identicalTo($tag));

        $beforeEvent = new Tags\BeforeDeleteTagEvent($tag);

        $this->eventDispatcher
            ->expects(self::at(0))
            ->method('dispatch')
            ->with(self::equalTo($beforeEvent))
            ->willReturn($beforeEvent);

        $this->eventDispatcher
            ->expects(self::at(1))
            ->method('dispatch')
            ->with(
                self::equalTo(new Tags\DeleteTagEvent($tag))
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $eventDispatchingService->deleteTag($tag);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::newTagCreateStruct
     */
    public function testNewTagCreateStruct(): void
    {
        $this->tagsService
            ->expects(self::once())
            ->method('newTagCreateStruct')
            ->with(self::identicalTo(42), self::identicalTo('eng-GB'))
            ->willReturn(
                new TagCreateStruct(['parentTagId' => 42, 'mainLanguageCode' => 'eng-GB'])
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $tagCreateStruct = $eventDispatchingService->newTagCreateStruct(42, 'eng-GB');

        self::assertSame(42, $tagCreateStruct->parentTagId);
        self::assertSame('eng-GB', $tagCreateStruct->mainLanguageCode);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::newSynonymCreateStruct
     */
    public function testNewSynonymCreateStruct(): void
    {
        $this->tagsService
            ->expects(self::once())
            ->method('newSynonymCreateStruct')
            ->with(self::identicalTo(42), self::identicalTo('eng-GB'))
            ->willReturn(
                new SynonymCreateStruct(['mainTagId' => 42, 'mainLanguageCode' => 'eng-GB'])
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $synonymCreateStruct = $eventDispatchingService->newSynonymCreateStruct(42, 'eng-GB');

        self::assertSame(42, $synonymCreateStruct->mainTagId);
        self::assertSame('eng-GB', $synonymCreateStruct->mainLanguageCode);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::newTagUpdateStruct
     */
    public function testNewTagUpdateStruct(): void
    {
        $this->tagsService
            ->expects(self::once())
            ->method('newTagUpdateStruct')
            ->willReturn(
                new TagUpdateStruct()
            );

        $eventDispatchingService = $this->getEventDispatchingService();
        $tagUpdateStruct = $eventDispatchingService->newTagUpdateStruct();

        self::assertCount(0, $tagUpdateStruct->getKeywords());
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Event\TagsService::sudo
     */
    public function testSudo(): void
    {
        $callback = static function (): void {
        };

        $this->tagsService
            ->expects(self::once())
            ->method('sudo')
            ->willReturn('some_value');

        $eventDispatchingService = $this->getEventDispatchingService();
        $value = $eventDispatchingService->sudo($callback);

        self::assertSame('some_value', $value);
    }

    private function getEventDispatchingService(): TagsService
    {
        return new TagsService($this->tagsService, $this->eventDispatcher);
    }
}
