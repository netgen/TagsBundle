<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\API\Repository;

use DateTimeImmutable;
use DateTimeInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\PropertyReadOnlyException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\User\User as IbexaUser;
use Ibexa\Core\Base\Exceptions\InvalidArgumentValue;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\User\User;
use Ibexa\Tests\Integration\Core\Repository\BaseTest;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

use function array_pop;
use function count;
use function explode;
use function implode;
use function time;
use function trim;

use const PHP_INT_MAX;

abstract class BaseTagsServiceTest extends BaseTest
{
    protected Repository $repository;

    protected TagsService $tagsService;

    /**
     * @covers \Netgen\TagsBundle\API\Repository\Values\Tags\Tag::__get
     */
    public function testMissingProperty(): void
    {
        $this->expectException(PropertyNotFoundException::class);

        $tag = new Tag();
        $tag->notDefined = 42;
    }

    /**
     * @covers \Netgen\TagsBundle\API\Repository\Values\Tags\Tag::__set
     */
    public function testReadOnlyProperty(): void
    {
        $this->expectException(PropertyReadOnlyException::class);

        $tag = new Tag();
        $tag->id = 42;
    }

    /**
     * @covers \Netgen\TagsBundle\API\Repository\Values\Tags\Tag::__isset
     */
    public function testIsPropertySet(): void
    {
        $tag = new Tag(['id' => 42]);
        $value = isset($tag->notDefined);
        self::assertFalse($value);

        $value = isset($tag->id);
        self::assertTrue($value);
    }

    /**
     * @covers \Netgen\TagsBundle\API\Repository\Values\Tags\Tag::__unset
     */
    public function testUnsetProperty(): void
    {
        $this->expectException(PropertyReadOnlyException::class);

        $tag = new Tag(['id' => 2]);
        $tag->id = null;
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::newTagCreateStruct
     */
    public function testNewTagCreateStruct(): void
    {
        $tagCreateStruct = $this->tagsService->newTagCreateStruct(42, 'eng-GB');

        $this->assertPropertiesCorrect(
            [
                'parentTagId' => 42,
                'mainLanguageCode' => 'eng-GB',
                'remoteId' => null,
                'alwaysAvailable' => true,
                'keywords' => [],
            ],
            $tagCreateStruct
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::newTagCreateStruct
     */
    public function testNewSynonymCreateStruct(): void
    {
        $synonymCreateStruct = $this->tagsService->newSynonymCreateStruct(42, 'eng-GB');

        $this->assertPropertiesCorrect(
            [
                'mainTagId' => 42,
                'mainLanguageCode' => 'eng-GB',
                'remoteId' => null,
                'alwaysAvailable' => true,
                'keywords' => [],
            ],
            $synonymCreateStruct
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::newTagUpdateStruct
     */
    public function testNewTagUpdateStruct(): void
    {
        $tagUpdateStruct = $this->tagsService->newTagUpdateStruct();

        $this->assertPropertiesCorrect(
            [
                'keywords' => [],
                'remoteId' => null,
                'alwaysAvailable' => null,
            ],
            $tagUpdateStruct
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTag
     */
    public function testLoadTag(): void
    {
        $tag = $this->tagsService->loadTag(40);

        $this->assertPropertiesCorrect(
            [
                'id' => 40,
                'parentTagId' => 7,
                'mainTagId' => 0,
                'keywords' => ['eng-GB' => 'eztags'],
                'depth' => 3,
                'pathString' => '/8/7/40/',
                'modificationDate' => $this->getDateTime(1308153110),
                'remoteId' => '182be0c5cdcd5072bb1864cdee4d3d6e',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => false,
                'languageCodes' => ['eng-GB'],
                'prioritizedLanguageCode' => null,
            ],
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTag
     */
    public function testLoadTagThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->tagsService->loadTag(PHP_INT_MAX);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTag
     */
    public function testLoadTagThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->loadTag(40);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagByRemoteId
     */
    public function testLoadTagByRemoteId(): void
    {
        // $this->markTestSkipped( "Fails for unknown reason!" );

        $tag = $this->tagsService->loadTagByRemoteId('182be0c5cdcd5072bb1864cdee4d3d6e');

        $this->assertPropertiesCorrect(
            [
                'id' => 40,
                'parentTagId' => 7,
                'mainTagId' => 0,
                'keywords' => ['eng-GB' => 'eztags'],
                'depth' => 3,
                'pathString' => '/8/7/40/',
                'modificationDate' => $this->getDateTime(1308153110),
                'remoteId' => '182be0c5cdcd5072bb1864cdee4d3d6e',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => false,
                'languageCodes' => ['eng-GB'],
                'prioritizedLanguageCode' => null,
            ],
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagByRemoteId
     */
    public function testLoadTagByRemoteIdThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->tagsService->loadTagByRemoteId('Non-existing remote ID');
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagByRemoteId
     */
    public function testLoadTagByRemoteIdThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->loadTagByRemoteId('182be0c5cdcd5072bb1864cdee4d3d6e');
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagByUrl
     */
    public function testLoadTagByUrl(): void
    {
        $tag = $this->tagsService->loadTagByUrl('ez publish/extensions/eztags', ['eng-GB']);

        $this->assertPropertiesCorrect(
            [
                'id' => 40,
                'parentTagId' => 7,
                'mainTagId' => 0,
                'keywords' => ['eng-GB' => 'eztags'],
                'depth' => 3,
                'pathString' => '/8/7/40/',
                'modificationDate' => $this->getDateTime(1308153110),
                'remoteId' => '182be0c5cdcd5072bb1864cdee4d3d6e',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => false,
                'languageCodes' => ['eng-GB'],
                'prioritizedLanguageCode' => 'eng-GB',
            ],
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagByUrl
     */
    public function testLoadTagByUrlThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->tagsService->loadTagByUrl('does/not/exist', ['eng-GB']);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagByUrl
     */
    public function testLoadTagByUrlThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->loadTagByUrl('ez publish/extensions/eztags', ['eng-GB']);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagChildren
     *
     * @depends testLoadTag
     */
    public function testLoadTagChildren(): void
    {
        $tag = $this->tagsService->loadTag(16);
        $children = $this->tagsService->loadTagChildren($tag);

        self::assertCount(6, $children);
        self::assertContainsOnlyInstancesOf(Tag::class, $children);

        foreach ($children as $child) {
            self::assertSame($tag->id, $child->parentTagId);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagChildren
     *
     * @depends testLoadTag
     */
    public function testLoadTagChildrenFromRoot(): void
    {
        $children = $this->tagsService->loadTagChildren();

        self::assertCount(9, $children);
        self::assertContainsOnlyInstancesOf(Tag::class, $children);

        foreach ($children as $child) {
            self::assertSame(0, $child->parentTagId);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagChildren
     */
    public function testLoadTagChildrenThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->loadTagChildren(
            new Tag(
                ['id' => 16]
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagChildrenCount
     *
     * @depends testLoadTag
     */
    public function testGetTagChildrenCount(): void
    {
        $childrenCount = $this->tagsService->getTagChildrenCount(
            $tag = $this->tagsService->loadTag(16)
        );

        self::assertSame(6, $childrenCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagChildrenCount
     *
     * @depends testLoadTag
     */
    public function testGetTagChildrenCountFromRoot(): void
    {
        $childrenCount = $this->tagsService->getTagChildrenCount();

        self::assertSame(9, $childrenCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagChildrenCount
     */
    public function testGetTagChildrenCountThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->getTagChildrenCount(
            new Tag(
                ['id' => 16]
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagsByKeyword
     *
     * @depends testLoadTag
     */
    public function testLoadTagsByKeyword(): void
    {
        $tags = $this->tagsService->loadTagsByKeyword('eztags', 'eng-GB');

        self::assertCount(2, $tags);
        self::assertContainsOnlyInstancesOf(Tag::class, $tags);

        foreach ($tags as $tag) {
            self::assertSame(['eng-GB' => 'eztags'], $tag->keywords);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagsByKeyword
     */
    public function testLoadTagsByKeywordThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->loadTagsByKeyword('eztags', 'eng-GB');
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagsByKeywordCount
     *
     * @depends testLoadTag
     */
    public function testGetTagsByKeywordCount(): void
    {
        $tagsCount = $this->tagsService->getTagsByKeywordCount('eztags', 'eng-GB');

        self::assertSame(2, $tagsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagsByKeywordCount
     */
    public function testGetTagsByKeywordCountThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->getTagsByKeywordCount('eztags', 'eng-GB');
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagSynonyms
     *
     * @depends testLoadTag
     */
    public function testLoadTagSynonyms(): void
    {
        $tag = $this->tagsService->loadTag(16);
        $synonyms = $this->tagsService->loadTagSynonyms($tag);

        self::assertCount(2, $synonyms);
        self::assertContainsOnlyInstancesOf(Tag::class, $synonyms);

        foreach ($synonyms as $synonym) {
            self::assertSame($tag->id, $synonym->mainTagId);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagSynonyms
     *
     * @depends testLoadTag
     */
    public function testLoadTagSynonymsThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->tagsService->loadTagSynonyms(
            $this->tagsService->loadTag(95)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagSynonyms
     */
    public function testLoadTagSynonymsThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->loadTagSynonyms(
            new Tag(
                ['id' => 94]
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagSynonymCount
     *
     * @depends testLoadTag
     */
    public function testGetTagSynonymCount(): void
    {
        $synonymsCount = $this->tagsService->getTagSynonymCount(
            $this->tagsService->loadTag(16)
        );

        self::assertSame(2, $synonymsCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagSynonymCount
     *
     * @depends testLoadTag
     */
    public function testGetTagSynonymCountThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->tagsService->getTagSynonymCount(
            $this->tagsService->loadTag(95)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagSynonymCount
     */
    public function testGetTagSynonymCountThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->getTagSynonymCount(
            new Tag(
                ['id' => 94]
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContent
     *
     * @depends testLoadTag
     */
    public function testGetRelatedContent(): void
    {
        $tag = $this->tagsService->loadTag(16);
        $content = $this->tagsService->getRelatedContent($tag);

        self::assertCount(3, $content);
        self::assertContainsOnlyInstancesOf(ContentInfo::class, $content);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContent
     *
     * @depends testLoadTag
     */
    public function testGetRelatedContentNoContent(): void
    {
        $tag = $this->tagsService->loadTag(42);
        $content = $this->tagsService->getRelatedContent($tag);

        self::assertCount(0, $content);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContent
     *
     * @depends testLoadTag
     */
    public function testGetRelatedContentThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->tagsService->getRelatedContent(
            $this->tagsService->loadTag(PHP_INT_MAX)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContent
     */
    public function testGetRelatedContentThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->getRelatedContent(
            new Tag(
                ['id' => 40]
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContentCount
     *
     * @depends testLoadTag
     */
    public function testGetRelatedContentCount(): void
    {
        $contentCount = $this->tagsService->getRelatedContentCount(
            $this->tagsService->loadTag(16)
        );

        self::assertSame(3, $contentCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContentCount
     *
     * @depends testLoadTag
     */
    public function testGetRelatedContentCountNoContent(): void
    {
        $contentCount = $this->tagsService->getRelatedContentCount(
            $this->tagsService->loadTag(42)
        );

        self::assertSame(0, $contentCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContentCount
     *
     * @depends testLoadTag
     */
    public function testGetRelatedContentCountThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->tagsService->getRelatedContentCount(
            $this->tagsService->loadTag(PHP_INT_MAX)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContentCount
     */
    public function testGetRelatedContentCountThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->getRelatedContentCount(
            new Tag(
                ['id' => 40]
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::createTag
     *
     * @depends testNewTagCreateStruct
     */
    public function testCreateTag(): void
    {
        $createStruct = $this->tagsService->newTagCreateStruct(40, 'eng-GB');
        $createStruct->setKeyword('Test tag');
        $createStruct->alwaysAvailable = true;
        $createStruct->remoteId = 'New remote ID';

        $createdTag = $this->tagsService->createTag($createStruct);

        $this->assertPropertiesCorrect(
            [
                'id' => 97,
                'parentTagId' => 40,
                'mainTagId' => 0,
                'keywords' => ['eng-GB' => 'Test tag'],
                'depth' => 4,
                'pathString' => '/8/7/40/97/',
                'remoteId' => 'New remote ID',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
                'languageCodes' => ['eng-GB'],
                'prioritizedLanguageCode' => null,
            ],
            $createdTag
        );

        self::assertGreaterThan(0, $createdTag->modificationDate->getTimestamp());
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::createTag
     *
     * @depends testNewTagCreateStruct
     */
    public function testCreateTagWithNoParent(): void
    {
        $createStruct = $this->tagsService->newTagCreateStruct(0, 'eng-GB');
        $createStruct->setKeyword('Test tag');
        $createStruct->alwaysAvailable = true;
        $createStruct->remoteId = 'New remote ID';

        $createdTag = $this->tagsService->createTag($createStruct);

        $this->assertPropertiesCorrect(
            [
                'id' => 97,
                'parentTagId' => 0,
                'mainTagId' => 0,
                'keywords' => ['eng-GB' => 'Test tag'],
                'depth' => 1,
                'pathString' => '/97/',
                'remoteId' => 'New remote ID',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
                'languageCodes' => ['eng-GB'],
                'prioritizedLanguageCode' => null,
            ],
            $createdTag
        );

        self::assertGreaterThan(0, $createdTag->modificationDate->getTimestamp());
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::createTag
     *
     * @depends testNewTagCreateStruct
     */
    public function testCreateTagThrowsInvalidArgumentValueInvalidLanguageCode(): void
    {
        $this->expectException(InvalidArgumentValue::class);

        $createStruct = $this->tagsService->newTagCreateStruct(40, '');
        $this->tagsService->createTag($createStruct);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::createTag
     *
     * @depends testNewTagCreateStruct
     */
    public function testCreateTagThrowsInvalidArgumentValueInvalidRemoteId(): void
    {
        $this->expectException(InvalidArgumentValue::class);

        $createStruct = $this->tagsService->newTagCreateStruct(40, 'eng-GB');
        $createStruct->remoteId = 'remoteId';
        $this->tagsService->createTag($createStruct);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::createTag
     *
     * @depends testNewTagCreateStruct
     */
    public function testCreateTagThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $createStruct = $this->tagsService->newTagCreateStruct(40, 'eng-GB');
        $createStruct->remoteId = '182be0c5cdcd5072bb1864cdee4d3d6e';

        $this->tagsService->createTag($createStruct);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::createTag
     *
     * @depends testNewTagCreateStruct
     */
    public function testCreateTagThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));

        $createStruct = $this->tagsService->newTagCreateStruct(40, 'eng-GB');
        $createStruct->remoteId = 'New remote ID';

        $this->tagsService->createTag($createStruct);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::updateTag
     *
     * @depends testLoadTag
     * @depends testNewTagUpdateStruct
     */
    public function testUpdateTag(): void
    {
        $tag = $this->tagsService->loadTag(40);

        $updateStruct = $this->tagsService->newTagUpdateStruct();
        $updateStruct->setKeyword('New keyword', 'eng-GB');
        $updateStruct->setKeyword('New keyword US', 'eng-US');
        $updateStruct->mainLanguageCode = 'eng-US';
        $updateStruct->alwaysAvailable = true;
        $updateStruct->remoteId = 'New remote ID';

        $updatedTag = $this->tagsService->updateTag(
            $tag,
            $updateStruct
        );

        $this->assertPropertiesCorrect(
            [
                'id' => 40,
                'parentTagId' => 7,
                'mainTagId' => 0,
                'keywords' => ['eng-US' => 'New keyword US', 'eng-GB' => 'New keyword'],
                'depth' => 3,
                'pathString' => '/8/7/40/',
                'remoteId' => 'New remote ID',
                'mainLanguageCode' => 'eng-US',
                'alwaysAvailable' => true,
                'languageCodes' => ['eng-US', 'eng-GB'],
                'prioritizedLanguageCode' => null,
            ],
            $updatedTag
        );

        self::assertGreaterThan($tag->modificationDate->getTimestamp(), $updatedTag->modificationDate->getTimestamp());
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::updateTag
     *
     * @depends testNewTagUpdateStruct
     */
    public function testUpdateTagThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $updateStruct = $this->tagsService->newTagUpdateStruct();
        $updateStruct->mainLanguageCode = 'eng-GB';
        $updateStruct->setKeyword('New keyword');
        $updateStruct->remoteId = 'New remote ID';

        $this->tagsService->updateTag(
            new Tag(
                [
                    'id' => PHP_INT_MAX,
                    'mainTagId' => 0,
                ]
            ),
            $updateStruct
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::updateTag
     *
     * @depends testLoadTag
     * @depends testNewTagUpdateStruct
     */
    public function testUpdateTagThrowsInvalidArgumentValueInvalidKeyword(): void
    {
        $this->expectException(InvalidArgumentValue::class);

        $tag = $this->tagsService->loadTag(40);

        $updateStruct = $this->tagsService->newTagUpdateStruct();
        $updateStruct->mainLanguageCode = 'eng-GB';
        $updateStruct->setKeyword('');
        $updateStruct->remoteId = 'e2c420d928d4bf8ce0ff2ec19b371514';

        $this->tagsService->updateTag(
            $tag,
            $updateStruct
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::updateTag
     *
     * @depends testLoadTag
     * @depends testNewTagUpdateStruct
     */
    public function testUpdateTagThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $tag = $this->tagsService->loadTag(40);

        $updateStruct = $this->tagsService->newTagUpdateStruct();
        $updateStruct->mainLanguageCode = 'eng-GB';
        $updateStruct->setKeyword('New keyword');
        $updateStruct->remoteId = 'e2c420d928d4bf8ce0ff2ec19b371514';

        $this->tagsService->updateTag(
            $tag,
            $updateStruct
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::updateTag
     *
     * @depends testNewTagUpdateStruct
     */
    public function testUpdateTagThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));

        $updateStruct = $this->tagsService->newTagUpdateStruct();
        $updateStruct->mainLanguageCode = 'eng-GB';
        $updateStruct->setKeyword('New keyword');
        $updateStruct->remoteId = 'New remote ID';

        $this->tagsService->updateTag(
            new Tag(
                [
                    'id' => 40,
                    'mainTagId' => 0,
                ]
            ),
            $updateStruct
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::updateTag
     *
     * @depends testNewTagUpdateStruct
     */
    public function testUpdateTagThrowsUnauthorizedExceptionForSynonym(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));

        $updateStruct = $this->tagsService->newTagUpdateStruct();
        $updateStruct->mainLanguageCode = 'eng-GB';
        $updateStruct->setKeyword('New keyword');
        $updateStruct->remoteId = 'New remote ID';

        $this->tagsService->updateTag(
            new Tag(
                [
                    'id' => 95,
                    'mainTagId' => 0,
                ]
            ),
            $updateStruct
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::addSynonym
     *
     * @depends testLoadTag
     */
    public function testAddSynonym(): void
    {
        $createStruct = $this->tagsService->newSynonymCreateStruct(40, 'eng-GB');
        $createStruct->setKeyword('New synonym');
        $createStruct->alwaysAvailable = true;
        $createStruct->remoteId = 'New remote ID';

        $createdSynonym = $this->tagsService->addSynonym($createStruct);

        $this->assertPropertiesCorrect(
            [
                'id' => 97,
                'parentTagId' => 7,
                'mainTagId' => 40,
                'keywords' => ['eng-GB' => 'New synonym'],
                'depth' => 3,
                'pathString' => '/8/7/97/',
                'remoteId' => 'New remote ID',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
                'languageCodes' => ['eng-GB'],
                'prioritizedLanguageCode' => null,
            ],
            $createdSynonym
        );

        self::assertGreaterThan(0, $createdSynonym->modificationDate->getTimestamp());
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::addSynonym
     */
    public function testAddSynonymThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $createStruct = $this->tagsService->newSynonymCreateStruct(PHP_INT_MAX, 'eng-GB');
        $createStruct->setKeyword('Test tag');

        $this->tagsService->addSynonym($createStruct);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::addSynonym
     *
     * @depends testLoadTag
     */
    public function testAddSynonymThrowsInvalidArgumentValueInvalidKeyword(): void
    {
        $this->expectException(InvalidArgumentValue::class);

        $createStruct = $this->tagsService->newSynonymCreateStruct(95, 'eng-GB');
        $createStruct->setKeyword('');

        $this->tagsService->addSynonym($createStruct);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::addSynonym
     *
     * @depends testLoadTag
     */
    public function testAddSynonymThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $createStruct = $this->tagsService->newSynonymCreateStruct(95, 'eng-GB');
        $createStruct->setKeyword('New synonym');

        $this->tagsService->addSynonym($createStruct);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::addSynonym
     */
    public function testAddSynonymThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $createStruct = $this->tagsService->newSynonymCreateStruct(40, 'eng-GB');
        $createStruct->setKeyword('New synonym');

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->addSynonym($createStruct);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::convertToSynonym
     *
     * @depends testLoadTag
     * @depends testGetTagSynonymCount
     * @depends testGetTagChildrenCount
     */
    public function testConvertToSynonym(): void
    {
        $tag = $this->tagsService->loadTag(16);
        $mainTag = $this->tagsService->loadTag(40);

        $convertedSynonym = $this->tagsService->convertToSynonym($tag, $mainTag);

        $this->assertPropertiesCorrect(
            [
                'id' => $tag->id,
                'parentTagId' => $mainTag->parentTagId,
                'mainTagId' => $mainTag->id,
                'keyword' => $tag->keyword,
                'depth' => $mainTag->depth,
                'pathString' => $this->getSynonymPathString($convertedSynonym->id, $mainTag->pathString),
                'remoteId' => $tag->remoteId,
            ],
            $convertedSynonym
        );

        self::assertGreaterThan($tag->modificationDate->getTimestamp(), $convertedSynonym->modificationDate->getTimestamp());

        $synonymsCount = $this->tagsService->getTagSynonymCount($mainTag);
        self::assertSame(3, $synonymsCount);

        $childrenCount = $this->tagsService->getTagChildrenCount($mainTag);
        self::assertSame(6, $childrenCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::convertToSynonym
     */
    public function testConvertToSynonymThrowsNotFoundException(): void
    {
        try {
            $this->tagsService->convertToSynonym(
                new Tag(
                    [
                        'id' => PHP_INT_MAX,
                    ]
                ),
                new Tag(
                    [
                        'id' => 40,
                    ]
                )
            );
            self::fail('First tag was found');
        } catch (NotFoundException) {
            // Do nothing
        }

        try {
            $this->tagsService->convertToSynonym(
                new Tag(
                    [
                        'id' => 16,
                    ]
                ),
                new Tag(
                    [
                        'id' => PHP_INT_MAX,
                    ]
                )
            );
            self::fail('Second tag was found');
        } catch (NotFoundException) {
            // Do nothing
        }

        // Fake assertion count to remove the risky flag
        $this->addToAssertionCount(1);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::convertToSynonym
     *
     * @depends testLoadTag
     */
    public function testConvertToSynonymThrowsInvalidArgumentExceptionTagsAreSynonyms(): void
    {
        try {
            $this->tagsService->convertToSynonym(
                $this->tagsService->loadTag(95),
                $this->tagsService->loadTag(40)
            );
            self::fail('First tag is a synonym');
        } catch (InvalidArgumentException) {
            // Do nothing
        }

        try {
            $this->tagsService->convertToSynonym(
                $this->tagsService->loadTag(16),
                $this->tagsService->loadTag(95)
            );
            self::fail('Second tag is a synonym');
        } catch (InvalidArgumentException) {
            // Do nothing
        }

        // Fake assertion count to remove the risky flag
        $this->addToAssertionCount(1);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::convertToSynonym
     *
     * @depends testLoadTag
     */
    public function testConvertToSynonymThrowsInvalidArgumentExceptionMainTagBelowTag(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->tagsService->convertToSynonym(
            $this->tagsService->loadTag(7),
            $this->tagsService->loadTag(40)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::convertToSynonym
     */
    public function testConvertToSynonymThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->convertToSynonym(
            new Tag(
                [
                    'id' => 16,
                ]
            ),
            new Tag(
                [
                    'id' => 40,
                ]
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::mergeTags
     *
     * @depends testLoadTag
     * @depends testGetRelatedContentCount
     * @depends testGetTagChildrenCount
     */
    public function testMergeTags(): void
    {
        $tag = $this->tagsService->loadTag(16);
        $targetTag = $this->tagsService->loadTag(40);

        $this->tagsService->mergeTags($tag, $targetTag);

        try {
            $this->tagsService->loadTag($tag->id);
            self::fail('Tag not deleted after merging');
        } catch (NotFoundException) {
            // Do nothing
        }

        $relatedObjectsCount = $this->tagsService->getRelatedContentCount($targetTag);
        self::assertSame(3, $relatedObjectsCount);

        $childrenCount = $this->tagsService->getTagChildrenCount($targetTag);
        self::assertSame(6, $childrenCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::mergeTags
     */
    public function testMergeTagsThrowsNotFoundException(): void
    {
        try {
            $this->tagsService->mergeTags(
                new Tag(
                    [
                        'id' => PHP_INT_MAX,
                    ]
                ),
                new Tag(
                    [
                        'id' => 40,
                    ]
                )
            );
            self::fail('First tag was found');
        } catch (NotFoundException) {
            // Do nothing
        }

        try {
            $this->tagsService->mergeTags(
                new Tag(
                    [
                        'id' => 16,
                    ]
                ),
                new Tag(
                    [
                        'id' => PHP_INT_MAX,
                    ]
                )
            );
            self::fail('Second tag was found');
        } catch (NotFoundException) {
            // Do nothing
        }

        // Fake assertion count to remove the risky flag
        $this->addToAssertionCount(1);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::mergeTags
     *
     * @depends testLoadTag
     */
    public function testMergeTagsThrowsInvalidArgumentExceptionTagsAreSynonyms(): void
    {
        try {
            $this->tagsService->mergeTags(
                $this->tagsService->loadTag(95),
                $this->tagsService->loadTag(40)
            );
            self::fail('First tag is a synonym');
        } catch (InvalidArgumentException) {
            // Do nothing
        }

        try {
            $this->tagsService->mergeTags(
                $this->tagsService->loadTag(16),
                $this->tagsService->loadTag(95)
            );
            self::fail('Second tag is a synonym');
        } catch (InvalidArgumentException) {
            // Do nothing
        }

        // Fake assertion count to remove the risky flag
        $this->addToAssertionCount(1);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::mergeTags
     *
     * @depends testLoadTag
     */
    public function testMergeTagsThrowsInvalidArgumentExceptionTargetTagBelowTag(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->tagsService->mergeTags(
            $this->tagsService->loadTag(7),
            $this->tagsService->loadTag(40)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::mergeTags
     */
    public function testMergeTagsThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->mergeTags(
            new Tag(
                [
                    'id' => 16,
                ]
            ),
            new Tag(
                [
                    'id' => 40,
                ]
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::copySubtree
     */
    public function testCopySubtree(): void
    {
        self::markTestIncomplete('@TODO: Implement test for copySubtree');
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::copySubtree
     */
    public function testCopySubtreeThrowsNotFoundException(): void
    {
        try {
            $this->tagsService->copySubtree(
                new Tag(
                    [
                        'id' => PHP_INT_MAX,
                        'parentTagId' => 42,
                    ]
                ),
                new Tag(
                    [
                        'id' => 40,
                        'parentTagId' => 24,
                    ]
                )
            );
            self::fail('First tag was found');
        } catch (NotFoundException) {
            // Do nothing
        }

        try {
            $this->tagsService->copySubtree(
                new Tag(
                    [
                        'id' => 16,
                        'parentTagId' => 42,
                    ]
                ),
                new Tag(
                    [
                        'id' => PHP_INT_MAX,
                        'parentTagId' => 24,
                    ]
                )
            );
            self::fail('Second tag was found');
        } catch (NotFoundException) {
            // Do nothing
        }

        // Fake assertion count to remove the risky flag
        $this->addToAssertionCount(1);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::copySubtree
     *
     * @depends testLoadTag
     */
    public function testCopySubtreeThrowsInvalidArgumentExceptionTagsAreSynonyms(): void
    {
        try {
            $this->tagsService->copySubtree(
                $this->tagsService->loadTag(95),
                $this->tagsService->loadTag(40)
            );
            self::fail('First tag is a synonym');
        } catch (InvalidArgumentException) {
            // Do nothing
        }

        try {
            $this->tagsService->copySubtree(
                $this->tagsService->loadTag(16),
                $this->tagsService->loadTag(95)
            );
            self::fail('Second tag is a synonym');
        } catch (InvalidArgumentException) {
            // Do nothing
        }

        // Fake assertion count to remove the risky flag
        $this->addToAssertionCount(1);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::copySubtree
     *
     * @depends testLoadTag
     */
    public function testCopySubtreeThrowsInvalidArgumentExceptionTargetTagBelowTag(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->tagsService->copySubtree(
            $this->tagsService->loadTag(7),
            $this->tagsService->loadTag(40)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::copySubtree
     *
     * @depends testLoadTag
     */
    public function testCopySubtreeThrowsInvalidArgumentExceptionTargetTagAlreadyParent(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->tagsService->copySubtree(
            $this->tagsService->loadTag(7),
            $this->tagsService->loadTag(8)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::copySubtree
     */
    public function testCopySubtreeThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->copySubtree(
            new Tag(
                [
                    'id' => 16,
                ]
            ),
            new Tag(
                [
                    'id' => 40,
                ]
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::moveSubtree
     *
     * @depends testLoadTag
     * @depends testLoadTagSynonyms
     */
    public function testMoveSubtree(): void
    {
        $tag = $this->tagsService->loadTag(16);
        $targetParentTag = $this->tagsService->loadTag(40);

        $movedTag = $this->tagsService->moveSubtree($tag, $targetParentTag);

        $this->assertPropertiesCorrect(
            [
                'id' => $tag->id,
                'parentTagId' => $targetParentTag->id,
                'mainTagId' => $tag->mainTagId,
                'keyword' => $tag->keyword,
                'depth' => $targetParentTag->depth + 1,
                'pathString' => $targetParentTag->pathString . $tag->id . '/',
                'remoteId' => $tag->remoteId,
            ],
            $movedTag
        );

        self::assertGreaterThan($tag->modificationDate->getTimestamp(), $movedTag->modificationDate->getTimestamp());

        foreach ($this->tagsService->loadTagSynonyms($movedTag) as $synonym) {
            self::assertSame($targetParentTag->id, $synonym->parentTagId);
            self::assertSame($targetParentTag->depth + 1, $synonym->depth);
            self::assertSame($targetParentTag->pathString . $synonym->id . '/', $synonym->pathString);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::moveSubtree
     */
    public function testMoveSubtreeThrowsNotFoundException(): void
    {
        try {
            $this->tagsService->moveSubtree(
                new Tag(
                    [
                        'id' => PHP_INT_MAX,
                        'parentTagId' => 42,
                    ]
                ),
                new Tag(
                    [
                        'id' => 40,
                        'parentTagId' => 24,
                    ]
                )
            );
            self::fail('First tag was found');
        } catch (NotFoundException) {
            // Do nothing
        }

        try {
            $this->tagsService->moveSubtree(
                new Tag(
                    [
                        'id' => 16,
                        'parentTagId' => 42,
                    ]
                ),
                new Tag(
                    [
                        'id' => PHP_INT_MAX,
                        'parentTagId' => 24,
                    ]
                )
            );
            self::fail('Second tag was found');
        } catch (NotFoundException) {
            // Do nothing
        }

        // Fake assertion count to remove the risky flag
        $this->addToAssertionCount(1);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::moveSubtree
     *
     * @depends testLoadTag
     */
    public function testMoveSubtreeThrowsInvalidArgumentExceptionTagsAreSynonyms(): void
    {
        try {
            $this->tagsService->moveSubtree(
                $this->tagsService->loadTag(95),
                $this->tagsService->loadTag(40)
            );
            self::fail('First tag is a synonym');
        } catch (InvalidArgumentException) {
            // Do nothing
        }

        try {
            $this->tagsService->moveSubtree(
                $this->tagsService->loadTag(16),
                $this->tagsService->loadTag(95)
            );
            self::fail('Second tag is a synonym');
        } catch (InvalidArgumentException) {
            // Do nothing
        }

        // Fake assertion count to remove the risky flag
        $this->addToAssertionCount(1);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::moveSubtree
     *
     * @depends testLoadTag
     */
    public function testMoveSubtreeThrowsInvalidArgumentExceptionTargetTagBelowTag(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->tagsService->moveSubtree(
            $this->tagsService->loadTag(7),
            $this->tagsService->loadTag(40)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::moveSubtree
     *
     * @depends testLoadTag
     */
    public function testMoveSubtreeThrowsInvalidArgumentExceptionTargetTagAlreadyParent(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->tagsService->moveSubtree(
            $this->tagsService->loadTag(7),
            $this->tagsService->loadTag(8)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::moveSubtree
     */
    public function testMoveSubtreeThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->moveSubtree(
            new Tag(
                [
                    'id' => 16,
                ]
            ),
            new Tag(
                [
                    'id' => 40,
                ]
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::deleteTag
     *
     * @depends testLoadTag
     * @depends testLoadTagSynonyms
     * @depends testLoadTagChildren
     */
    public function testDeleteTag(): void
    {
        $tag = $this->tagsService->loadTag(16);
        $tagSynonyms = $this->tagsService->loadTagSynonyms($tag);
        $tagChildren = $this->tagsService->loadTagChildren($tag);

        $this->tagsService->deleteTag($tag);

        try {
            $this->tagsService->loadTag($tag->id);
            self::fail('Tag not deleted');
        } catch (NotFoundException) {
            // Do nothing
        }

        foreach ($tagSynonyms as $synonym) {
            try {
                $this->tagsService->loadTag($synonym->id);
                self::fail('Synonym not deleted');
            } catch (NotFoundException) {
                // Do nothing
            }
        }

        foreach ($tagChildren as $child) {
            try {
                $this->tagsService->loadTag($child->id);
                self::fail('Child not deleted');
            } catch (NotFoundException) {
                // Do nothing
            }
        }

        // Fake assertion count to remove the risky flag
        $this->addToAssertionCount(1);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::deleteTag
     */
    public function testDeleteTagThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->tagsService->deleteTag(
            new Tag(
                [
                    'id' => PHP_INT_MAX,
                    'mainTagId' => 0,
                ]
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::deleteTag
     */
    public function testDeleteTagThrowsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->deleteTag(
            new Tag(
                [
                    'id' => 40,
                    'mainTagId' => 0,
                ]
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::deleteTag
     */
    public function testDeleteTagThrowsUnauthorizedExceptionForSynonym(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(10));
        $this->tagsService->deleteTag(
            new Tag(
                [
                    'id' => 95,
                    'mainTagId' => 0,
                ]
            )
        );
    }

    /**
     * Returns User stub with $id as User/Content id.
     */
    protected function getStubbedUser(int $id): IbexaUser
    {
        return new User(
            [
                'content' => new Content(
                    [
                        'versionInfo' => new VersionInfo(
                            [
                                'contentInfo' => new ContentInfo(['id' => $id]),
                            ]
                        ),
                        'internalFields' => [],
                    ]
                ),
            ]
        );
    }

    /**
     * Creates and returns a \DateTimeInterface object with received timestamp.
     */
    private function getDateTime(?int $timestamp = null): DateTimeInterface
    {
        return new DateTimeImmutable('@' . ($timestamp ?? time()));
    }

    /**
     * Returns the path string of a synonym for main tag path string.
     */
    private function getSynonymPathString(int $synonymId, string $mainTagPathString): string
    {
        $pathStringElements = explode('/', trim($mainTagPathString, '/'));
        array_pop($pathStringElements);

        return (count($pathStringElements) > 0 ? '/' . implode('/', $pathStringElements) : '') . '/' . $synonymId . '/';
    }
}
