<?php

namespace Netgen\TagsBundle\Tests\Core\Repository\Service\Integration;

use DateTime;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;
use eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Tests\Service\Integration\Base as BaseServiceTest;
use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;

/**
 * Test case for Tags Service.
 */
abstract class TagsBase extends BaseServiceTest
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * Test a new class and default values on properties.
     *
     * @covers \Netgen\TagsBundle\API\Repository\Values\Tags\Tag::__construct
     */
    public function testNewClass()
    {
        $tag = new Tag();

        $this->assertPropertiesCorrect(
            array(
                'id' => null,
                'parentTagId' => null,
                'mainTagId' => null,
                'keywords' => array(),
                'depth' => null,
                'pathString' => null,
                'modificationDate' => null,
                'remoteId' => null,
                'mainLanguageCode' => null,
                'alwaysAvailable' => null,
                'languageCodes' => array(),
            ),
            $tag
        );
    }

    /**
     * Test retrieving missing property.
     *
     * @covers \Netgen\TagsBundle\API\Repository\Values\Tags\Tag::__get
     */
    public function testMissingProperty()
    {
        try {
            $tag = new Tag();
            $value = $tag->notDefined;
            $this->fail('Succeeded getting non existing property');
        } catch (PropertyNotFoundException $e) {
        }
    }

    /**
     * Test setting read only property.
     *
     * @covers \Netgen\TagsBundle\API\Repository\Values\Tags\Tag::__set
     */
    public function testReadOnlyProperty()
    {
        try {
            $tag = new Tag();
            $tag->id = 42;
            $this->fail('Succeeded setting read only property');
        } catch (PropertyReadOnlyException $e) {
        }
    }

    /**
     * Test if property exists.
     *
     * @covers \Netgen\TagsBundle\API\Repository\Values\Tags\Tag::__isset
     */
    public function testIsPropertySet()
    {
        $tag = new Tag();
        $value = isset($tag->notDefined);
        $this->assertFalse($value);

        $value = isset($tag->id);
        $this->assertTrue($value);
    }

    /**
     * Test unsetting a property.
     *
     * @covers \Netgen\TagsBundle\API\Repository\Values\Tags\Tag::__unset
     */
    public function testUnsetProperty()
    {
        $tag = new Tag(array('id' => 2));
        try {
            unset($tag->id);
            $this->fail('Unsetting read-only property succeeded');
        } catch (PropertyReadOnlyException $e) {
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::newTagCreateStruct
     */
    public function testNewTagCreateStruct()
    {
        $tagCreateStruct = $this->tagsService->newTagCreateStruct(42, 'eng-GB');

        $this->assertInstanceOf(TagCreateStruct::class, $tagCreateStruct);

        $this->assertPropertiesCorrect(
            array(
                'parentTagId' => 42,
                'mainLanguageCode' => 'eng-GB',
                'remoteId' => null,
                'alwaysAvailable' => true,
                'keywords' => null,
            ),
            $tagCreateStruct
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::newTagCreateStruct
     */
    public function testNewSynonymCreateStruct()
    {
        $synonymCreateStruct = $this->tagsService->newSynonymCreateStruct(42, 'eng-GB');

        $this->assertInstanceOf(SynonymCreateStruct::class, $synonymCreateStruct);

        $this->assertPropertiesCorrect(
            array(
                'mainTagId' => 42,
                'mainLanguageCode' => 'eng-GB',
                'remoteId' => null,
                'alwaysAvailable' => true,
                'keywords' => null,
            ),
            $synonymCreateStruct
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::newTagUpdateStruct
     */
    public function testNewTagUpdateStruct()
    {
        $tagUpdateStruct = $this->tagsService->newTagUpdateStruct();

        $this->assertInstanceOf(TagUpdateStruct::class, $tagUpdateStruct);

        $this->assertPropertiesCorrect(
            array(
                'keywords' => null,
                'remoteId' => null,
                'mainLanguageCode' => null,
                'alwaysAvailable' => null,
            ),
            $tagUpdateStruct
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTag
     */
    public function testLoadTag()
    {
        $tag = $this->tagsService->loadTag(40);

        $this->assertInstanceOf(Tag::class, $tag);

        $this->assertPropertiesCorrect(
            array(
                'id' => 40,
                'parentTagId' => 7,
                'mainTagId' => 0,
                'keywords' => array('eng-GB' => 'eztags'),
                'depth' => 3,
                'pathString' => '/8/7/40/',
                'modificationDate' => $this->getDateTime(1308153110),
                'remoteId' => '182be0c5cdcd5072bb1864cdee4d3d6e',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => false,
                'languageCodes' => array('eng-GB'),
            ),
            $tag
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTag
     */
    public function testLoadTagThrowsNotFoundException()
    {
        $this->tagsService->loadTag(PHP_INT_MAX);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTag
     */
    public function testLoadTagThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->loadTag(40);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagByRemoteId
     */
    public function testLoadTagByRemoteId()
    {
        // $this->markTestSkipped( "Fails for unknown reason!" );

        $tag = $this->tagsService->loadTagByRemoteId('182be0c5cdcd5072bb1864cdee4d3d6e');

        $this->assertInstanceOf(Tag::class, $tag);

        $this->assertPropertiesCorrect(
            array(
                'id' => 40,
                'parentTagId' => 7,
                'mainTagId' => 0,
                'keywords' => array('eng-GB' => 'eztags'),
                'depth' => 3,
                'pathString' => '/8/7/40/',
                'modificationDate' => $this->getDateTime(1308153110),
                'remoteId' => '182be0c5cdcd5072bb1864cdee4d3d6e',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => false,
                'languageCodes' => array('eng-GB'),
            ),
            $tag
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagByRemoteId
     */
    public function testLoadTagByRemoteIdThrowsNotFoundException()
    {
        $this->tagsService->loadTagByRemoteId('Non-existing remote ID');
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagByRemoteId
     */
    public function testLoadTagByRemoteIdThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->loadTagByRemoteId('182be0c5cdcd5072bb1864cdee4d3d6e');
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagByUrl
     */
    public function testLoadTagByUrl()
    {
        $tag = $this->tagsService->loadTagByUrl('ez publish/extensions/eztags', array('eng-GB'));

        $this->assertInstanceOf(Tag::class, $tag);

        $this->assertPropertiesCorrect(
            array(
                'id' => 40,
                'parentTagId' => 7,
                'mainTagId' => 0,
                'keywords' => array('eng-GB' => 'eztags'),
                'depth' => 3,
                'pathString' => '/8/7/40/',
                'modificationDate' => $this->getDateTime(1308153110),
                'remoteId' => '182be0c5cdcd5072bb1864cdee4d3d6e',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => false,
                'languageCodes' => array('eng-GB'),
            ),
            $tag
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagByUrl
     */
    public function testLoadTagByUrlThrowsNotFoundException()
    {
        $this->tagsService->loadTagByUrl('does/not/exist', array('eng-GB'));
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagByUrl
     */
    public function testLoadTagByUrlThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->loadTagByUrl('ez publish/extensions/eztags', array('eng-GB'));
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagChildren
     * @depends testLoadTag
     */
    public function testLoadTagChildren()
    {
        $tag = $this->tagsService->loadTag(16);
        $children = $this->tagsService->loadTagChildren($tag);

        $this->assertInternalType('array', $children);
        $this->assertCount(6, $children);

        foreach ($children as $child) {
            $this->assertInstanceOf(Tag::class, $child);
            $this->assertEquals($tag->id, $child->parentTagId);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagChildren
     * @depends testLoadTag
     */
    public function testLoadTagChildrenFromRoot()
    {
        $children = $this->tagsService->loadTagChildren();

        $this->assertInternalType('array', $children);
        $this->assertCount(9, $children);

        foreach ($children as $child) {
            $this->assertInstanceOf(Tag::class, $child);
            $this->assertEquals(0, $child->parentTagId);
        }
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagChildren
     */
    public function testLoadTagChildrenThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->loadTagChildren(
            new Tag(
                array('id' => 16)
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagChildrenCount
     * @depends testLoadTag
     */
    public function testGetTagChildrenCount()
    {
        $childrenCount = $this->tagsService->getTagChildrenCount(
            $tag = $this->tagsService->loadTag(16)
        );

        $this->assertEquals(6, $childrenCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagChildrenCount
     * @depends testLoadTag
     */
    public function testGetTagChildrenCountFromRoot()
    {
        $childrenCount = $this->tagsService->getTagChildrenCount();

        $this->assertEquals(9, $childrenCount);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagChildrenCount
     */
    public function testGetTagChildrenCountThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->getTagChildrenCount(
            new Tag(
                array('id' => 16)
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagsByKeyword
     * @depends testLoadTag
     */
    public function testLoadTagsByKeyword()
    {
        $tags = $this->tagsService->loadTagsByKeyword('eztags', 'eng-GB');

        $this->assertInternalType('array', $tags);
        $this->assertCount(2, $tags);

        foreach ($tags as $tag) {
            $this->assertInstanceOf(Tag::class, $tag);
            $this->assertEquals(array('eng-GB' => 'eztags'), $tag->keywords);
        }
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagsByKeyword
     */
    public function testLoadTagsByKeywordThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->loadTagsByKeyword('eztags', 'eng-GB');
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagsByKeywordCount
     * @depends testLoadTag
     */
    public function testGetTagsByKeywordCount()
    {
        $tagsCount = $this->tagsService->getTagsByKeywordCount('eztags', 'eng-GB');

        $this->assertEquals(2, $tagsCount);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagsByKeywordCount
     */
    public function testGetTagsByKeywordCountThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->getTagsByKeywordCount('eztags', 'eng-GB');
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagSynonyms
     * @depends testLoadTag
     */
    public function testLoadTagSynonyms()
    {
        $tag = $this->tagsService->loadTag(16);
        $synonyms = $this->tagsService->loadTagSynonyms($tag);

        $this->assertInternalType('array', $synonyms);
        $this->assertCount(2, $synonyms);

        foreach ($synonyms as $synonym) {
            $this->assertInstanceOf(Tag::class, $synonym);
            $this->assertEquals($tag->id, $synonym->mainTagId);
        }
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagSynonyms
     * @depends testLoadTag
     */
    public function testLoadTagSynonymsThrowsInvalidArgumentException()
    {
        $this->tagsService->loadTagSynonyms(
            $this->tagsService->loadTag(95)
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::loadTagSynonyms
     */
    public function testLoadTagSynonymsThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->loadTagSynonyms(
            new Tag(
                array('id' => 94)
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagSynonymCount
     * @depends testLoadTag
     */
    public function testGetTagSynonymCount()
    {
        $synonymsCount = $this->tagsService->getTagSynonymCount(
            $this->tagsService->loadTag(16)
        );

        $this->assertEquals(2, $synonymsCount);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagSynonymCount
     * @depends testLoadTag
     */
    public function testGetTagSynonymCountThrowsInvalidArgumentException()
    {
        $this->tagsService->getTagSynonymCount(
            $this->tagsService->loadTag(95)
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getTagSynonymCount
     */
    public function testGetTagSynonymCountThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->getTagSynonymCount(
            new Tag(
                array('id' => 94)
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContent
     * @depends testLoadTag
     */
    public function testGetRelatedContent()
    {
        $tag = $this->tagsService->loadTag(16);
        $content = $this->tagsService->getRelatedContent($tag);

        $this->assertInternalType('array', $content);
        $this->assertCount(3, $content);

        foreach ($content as $contentItem) {
            $this->assertInstanceOf(ContentInfo::class, $contentItem);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContent
     * @depends testLoadTag
     */
    public function testGetRelatedContentNoContent()
    {
        $tag = $this->tagsService->loadTag(42);
        $content = $this->tagsService->getRelatedContent($tag);

        $this->assertInternalType('array', $content);
        $this->assertCount(0, $content);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContent
     * @depends testLoadTag
     */
    public function testGetRelatedContentThrowsNotFoundException()
    {
        $this->tagsService->getRelatedContent(
            $this->tagsService->loadTag(PHP_INT_MAX)
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContent
     */
    public function testGetRelatedContentThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->getRelatedContent(
            new Tag(
                array('id' => 40)
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContentCount
     * @depends testLoadTag
     */
    public function testGetRelatedContentCount()
    {
        $contentCount = $this->tagsService->getRelatedContentCount(
            $this->tagsService->loadTag(16)
        );

        $this->assertEquals(3, $contentCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContentCount
     * @depends testLoadTag
     */
    public function testGetRelatedContentCountNoContent()
    {
        $contentCount = $this->tagsService->getRelatedContentCount(
            $this->tagsService->loadTag(42)
        );

        $this->assertEquals(0, $contentCount);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContentCount
     * @depends testLoadTag
     */
    public function testGetRelatedContentCountThrowsNotFoundException()
    {
        $this->tagsService->getRelatedContentCount(
            $this->tagsService->loadTag(PHP_INT_MAX)
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::getRelatedContentCount
     */
    public function testGetRelatedContentCountThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->getRelatedContentCount(
            new Tag(
                array('id' => 40)
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::createTag
     * @depends testNewTagCreateStruct
     */
    public function testCreateTag()
    {
        $createStruct = $this->tagsService->newTagCreateStruct(40, 'eng-GB');
        $createStruct->setKeyword('Test tag');
        $createStruct->alwaysAvailable = true;
        $createStruct->remoteId = 'New remote ID';

        $createdTag = $this->tagsService->createTag($createStruct);

        $this->assertInstanceOf(Tag::class, $createdTag);

        $this->assertPropertiesCorrect(
            array(
                'id' => 97,
                'parentTagId' => 40,
                'mainTagId' => 0,
                'keywords' => array('eng-GB' => 'Test tag'),
                'depth' => 4,
                'pathString' => '/8/7/40/97/',
                'remoteId' => 'New remote ID',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
                'languageCodes' => array('eng-GB'),
            ),
            $createdTag
        );

        $this->assertInstanceOf('\\DateTime', $createdTag->modificationDate);
        $this->assertGreaterThan(0, $createdTag->modificationDate->getTimestamp());
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::createTag
     * @depends testNewTagCreateStruct
     */
    public function testCreateTagWithNoParent()
    {
        $createStruct = $this->tagsService->newTagCreateStruct(0, 'eng-GB');
        $createStruct->setKeyword('Test tag');
        $createStruct->alwaysAvailable = true;
        $createStruct->remoteId = 'New remote ID';

        $createdTag = $this->tagsService->createTag($createStruct);

        $this->assertInstanceOf(Tag::class, $createdTag);

        $this->assertPropertiesCorrect(
            array(
                'id' => 97,
                'parentTagId' => 0,
                'mainTagId' => 0,
                'keywords' => array('eng-GB' => 'Test tag'),
                'depth' => 1,
                'pathString' => '/97/',
                'remoteId' => 'New remote ID',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
                'languageCodes' => array('eng-GB'),
            ),
            $createdTag
        );

        $this->assertInstanceOf('\\DateTime', $createdTag->modificationDate);
        $this->assertGreaterThan(0, $createdTag->modificationDate->getTimestamp());
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::createTag
     * @depends testNewTagCreateStruct
     */
    public function testCreateTagThrowsInvalidArgumentValueInvalidLanguageCode()
    {
        $createStruct = $this->tagsService->newTagCreateStruct(40, '');
        $this->tagsService->createTag($createStruct);
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::createTag
     * @depends testNewTagCreateStruct
     */
    public function testCreateTagThrowsInvalidArgumentValueInvalidRemoteId()
    {
        $createStruct = $this->tagsService->newTagCreateStruct(40, 'eng-GB');
        $createStruct->remoteId = 42;
        $this->tagsService->createTag($createStruct);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::createTag
     * @depends testNewTagCreateStruct
     */
    public function testCreateTagThrowsInvalidArgumentException()
    {
        $createStruct = $this->tagsService->newTagCreateStruct(40, 'eng-GB');
        $createStruct->remoteId = '182be0c5cdcd5072bb1864cdee4d3d6e';

        $this->tagsService->createTag($createStruct);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::createTag
     * @depends testNewTagCreateStruct
     */
    public function testCreateTagThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));

        $createStruct = $this->tagsService->newTagCreateStruct(40, 'eng-GB');
        $createStruct->remoteId = 'New remote ID';

        $this->tagsService->createTag($createStruct);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::updateTag
     * @depends testLoadTag
     * @depends testNewTagUpdateStruct
     */
    public function testUpdateTag()
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

        $this->assertInstanceOf(Tag::class, $updatedTag);

        $this->assertPropertiesCorrect(
            array(
                'id' => 40,
                'parentTagId' => 7,
                'mainTagId' => 0,
                'keywords' => array('eng-US' => 'New keyword US', 'eng-GB' => 'New keyword'),
                'depth' => 3,
                'pathString' => '/8/7/40/',
                'remoteId' => 'New remote ID',
                'mainLanguageCode' => 'eng-US',
                'alwaysAvailable' => true,
                'languageCodes' => array('eng-US', 'eng-GB'),
            ),
            $updatedTag
        );

        $this->assertInstanceOf('\\DateTime', $updatedTag->modificationDate);
        $this->assertGreaterThan($tag->modificationDate->getTimestamp(), $updatedTag->modificationDate->getTimestamp());
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::updateTag
     * @depends testNewTagUpdateStruct
     */
    public function testUpdateTagThrowsNotFoundException()
    {
        $updateStruct = $this->tagsService->newTagUpdateStruct();
        $updateStruct->setKeyword('New keyword');
        $updateStruct->remoteId = 'New remote ID';

        $this->tagsService->updateTag(
            new Tag(
                array(
                    'id' => PHP_INT_MAX,
                )
            ),
            $updateStruct
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::updateTag
     * @depends testLoadTag
     * @depends testNewTagUpdateStruct
     */
    public function testUpdateTagThrowsInvalidArgumentValueInvalidKeyword()
    {
        $tag = $this->tagsService->loadTag(40);

        $updateStruct = $this->tagsService->newTagUpdateStruct();
        $updateStruct->setKeyword('');
        $updateStruct->remoteId = 'e2c420d928d4bf8ce0ff2ec19b371514';

        $this->tagsService->updateTag(
            $tag,
            $updateStruct
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::updateTag
     * @depends testLoadTag
     * @depends testNewTagUpdateStruct
     */
    public function testUpdateTagThrowsInvalidArgumentValueInvalidRemoteId()
    {
        $tag = $this->tagsService->loadTag(40);

        $updateStruct = $this->tagsService->newTagUpdateStruct();
        $updateStruct->setKeyword('New keyword');
        $updateStruct->remoteId = 42;

        $this->tagsService->updateTag(
            $tag,
            $updateStruct
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::updateTag
     * @depends testLoadTag
     * @depends testNewTagUpdateStruct
     */
    public function testUpdateTagThrowsInvalidArgumentException()
    {
        $tag = $this->tagsService->loadTag(40);

        $updateStruct = $this->tagsService->newTagUpdateStruct();
        $updateStruct->setKeyword('New keyword');
        $updateStruct->remoteId = 'e2c420d928d4bf8ce0ff2ec19b371514';

        $this->tagsService->updateTag(
            $tag,
            $updateStruct
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::updateTag
     * @depends testNewTagUpdateStruct
     */
    public function testUpdateTagThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));

        $updateStruct = $this->tagsService->newTagUpdateStruct();
        $updateStruct->setKeyword('New keyword');
        $updateStruct->remoteId = 'New remote ID';

        $this->tagsService->updateTag(
            new Tag(
                array(
                    'id' => 40,
                )
            ),
            $updateStruct
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::updateTag
     * @depends testNewTagUpdateStruct
     */
    public function testUpdateTagThrowsUnauthorizedExceptionForSynonym()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));

        $updateStruct = $this->tagsService->newTagUpdateStruct();
        $updateStruct->setKeyword('New keyword');
        $updateStruct->remoteId = 'New remote ID';

        $this->tagsService->updateTag(
            new Tag(
                array(
                    'id' => 95,
                )
            ),
            $updateStruct
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::addSynonym
     * @depends testLoadTag
     */
    public function testAddSynonym()
    {
        $createStruct = $this->tagsService->newSynonymCreateStruct(40, 'eng-GB');
        $createStruct->setKeyword('New synonym');
        $createStruct->alwaysAvailable = true;
        $createStruct->remoteId = 'New remote ID';

        $createdSynonym = $this->tagsService->addSynonym($createStruct);

        $this->assertInstanceOf(Tag::class, $createdSynonym);

        $this->assertPropertiesCorrect(
            array(
                'id' => 97,
                'parentTagId' => 7,
                'mainTagId' => 40,
                'keywords' => array('eng-GB' => 'New synonym'),
                'depth' => 3,
                'pathString' => '/8/7/97/',
                'remoteId' => 'New remote ID',
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
                'languageCodes' => array('eng-GB'),
            ),
            $createdSynonym
        );

        $this->assertInstanceOf('\\DateTime', $createdSynonym->modificationDate);
        $this->assertGreaterThan(0, $createdSynonym->modificationDate->getTimestamp());
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::addSynonym
     */
    public function testAddSynonymThrowsNotFoundException()
    {
        $createStruct = $this->tagsService->newSynonymCreateStruct(PHP_INT_MAX, 'eng-GB');
        $createStruct->setKeyword('Test tag');

        $this->tagsService->addSynonym($createStruct);
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::addSynonym
     * @depends testLoadTag
     */
    public function testAddSynonymThrowsInvalidArgumentValueInvalidKeyword()
    {
        $createStruct = $this->tagsService->newSynonymCreateStruct(95, 'eng-GB');
        $createStruct->setKeyword('');

        $this->tagsService->addSynonym($createStruct);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::addSynonym
     * @depends testLoadTag
     */
    public function testAddSynonymThrowsInvalidArgumentException()
    {
        $createStruct = $this->tagsService->newSynonymCreateStruct(95, 'eng-GB');
        $createStruct->setKeyword('New synonym');

        $this->tagsService->addSynonym($createStruct);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::addSynonym
     */
    public function testAddSynonymThrowsUnauthorizedException()
    {
        $createStruct = $this->tagsService->newSynonymCreateStruct(40, 'eng-GB');
        $createStruct->setKeyword('New synonym');

        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->addSynonym($createStruct);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::convertToSynonym
     * @depends testLoadTag
     * @depends testGetTagSynonymCount
     * @depends testGetTagChildrenCount
     */
    public function testConvertToSynonym()
    {
        $tag = $this->tagsService->loadTag(16);
        $mainTag = $this->tagsService->loadTag(40);

        $convertedSynonym = $this->tagsService->convertToSynonym($tag, $mainTag);

        $this->assertInstanceOf(Tag::class, $convertedSynonym);

        $this->assertPropertiesCorrect(
            array(
                'id' => $tag->id,
                'parentTagId' => $mainTag->parentTagId,
                'mainTagId' => $mainTag->id,
                'keyword' => $tag->keyword,
                'depth' => $mainTag->depth,
                'pathString' => $this->getSynonymPathString($convertedSynonym->id, $mainTag->pathString),
                'remoteId' => $tag->remoteId,
            ),
            $convertedSynonym
        );

        $this->assertInstanceOf('\\DateTime', $convertedSynonym->modificationDate);
        $this->assertGreaterThan($tag->modificationDate->getTimestamp(), $convertedSynonym->modificationDate->getTimestamp());

        $synonymsCount = $this->tagsService->getTagSynonymCount($mainTag);
        $this->assertEquals(3, $synonymsCount);

        $childrenCount = $this->tagsService->getTagChildrenCount($mainTag);
        $this->assertEquals(6, $childrenCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::convertToSynonym
     */
    public function testConvertToSynonymThrowsNotFoundException()
    {
        try {
            $this->tagsService->convertToSynonym(
                new Tag(
                    array(
                        'id' => PHP_INT_MAX,
                    )
                ),
                new Tag(
                    array(
                        'id' => 40,
                    )
                )
            );
            $this->fail('First tag was found');
        } catch (NotFoundException $e) {
            // Do nothing
        }

        try {
            $this->tagsService->convertToSynonym(
                new Tag(
                    array(
                        'id' => 16,
                    )
                ),
                new Tag(
                    array(
                        'id' => PHP_INT_MAX,
                    )
                )
            );
            $this->fail('Second tag was found');
        } catch (NotFoundException $e) {
            // Do nothing
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::convertToSynonym
     * @depends testLoadTag
     */
    public function testConvertToSynonymThrowsInvalidArgumentExceptionTagsAreSynonyms()
    {
        try {
            $this->tagsService->convertToSynonym(
                $this->tagsService->loadTag(95),
                $this->tagsService->loadTag(40)
            );
            $this->fail('First tag is a synonym');
        } catch (InvalidArgumentException $e) {
            // Do nothing
        }

        try {
            $this->tagsService->convertToSynonym(
                $this->tagsService->loadTag(16),
                $this->tagsService->loadTag(95)
            );
            $this->fail('Second tag is a synonym');
        } catch (InvalidArgumentException $e) {
            // Do nothing
        }
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::convertToSynonym
     * @depends testLoadTag
     */
    public function testConvertToSynonymThrowsInvalidArgumentExceptionMainTagBelowTag()
    {
        $this->tagsService->convertToSynonym(
            $this->tagsService->loadTag(7),
            $this->tagsService->loadTag(40)
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::convertToSynonym
     */
    public function testConvertToSynonymThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->convertToSynonym(
            new Tag(
                array(
                    'id' => 16,
                )
            ),
            new Tag(
                array(
                    'id' => 40,
                )
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::mergeTags
     * @depends testLoadTag
     * @depends testGetRelatedContentCount
     * @depends testGetTagChildrenCount
     */
    public function testMergeTags()
    {
        $tag = $this->tagsService->loadTag(16);
        $targetTag = $this->tagsService->loadTag(40);

        $this->tagsService->mergeTags($tag, $targetTag);

        try {
            $this->tagsService->loadTag($tag->id);
            $this->fail('Tag not deleted after merging');
        } catch (NotFoundException $e) {
            // Do nothing
        }

        $relatedObjectsCount = $this->tagsService->getRelatedContentCount($targetTag);
        $this->assertEquals(3, $relatedObjectsCount);

        $childrenCount = $this->tagsService->getTagChildrenCount($targetTag);
        $this->assertEquals(6, $childrenCount);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::mergeTags
     */
    public function testMergeTagsThrowsNotFoundException()
    {
        try {
            $this->tagsService->mergeTags(
                new Tag(
                    array(
                        'id' => PHP_INT_MAX,
                    )
                ),
                new Tag(
                    array(
                        'id' => 40,
                    )
                )
            );
            $this->fail('First tag was found');
        } catch (NotFoundException $e) {
            // Do nothing
        }

        try {
            $this->tagsService->mergeTags(
                new Tag(
                    array(
                        'id' => 16,
                    )
                ),
                new Tag(
                    array(
                        'id' => PHP_INT_MAX,
                    )
                )
            );
            $this->fail('Second tag was found');
        } catch (NotFoundException $e) {
            // Do nothing
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::mergeTags
     * @depends testLoadTag
     */
    public function testMergeTagsThrowsInvalidArgumentExceptionTagsAreSynonyms()
    {
        try {
            $this->tagsService->mergeTags(
                $this->tagsService->loadTag(95),
                $this->tagsService->loadTag(40)
            );
            $this->fail('First tag is a synonym');
        } catch (InvalidArgumentException $e) {
            // Do nothing
        }

        try {
            $this->tagsService->mergeTags(
                $this->tagsService->loadTag(16),
                $this->tagsService->loadTag(95)
            );
            $this->fail('Second tag is a synonym');
        } catch (InvalidArgumentException $e) {
            // Do nothing
        }
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::mergeTags
     * @depends testLoadTag
     */
    public function testMergeTagsThrowsInvalidArgumentExceptionTargetTagBelowTag()
    {
        $this->tagsService->mergeTags(
            $this->tagsService->loadTag(7),
            $this->tagsService->loadTag(40)
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::mergeTags
     */
    public function testMergeTagsThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->mergeTags(
            new Tag(
                array(
                    'id' => 16,
                )
            ),
            new Tag(
                array(
                    'id' => 40,
                )
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::copySubtree
     */
    public function testCopySubtree()
    {
        $this->markTestIncomplete('@TODO: Implement test for copySubtree');
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::copySubtree
     */
    public function testCopySubtreeThrowsNotFoundException()
    {
        try {
            $this->tagsService->copySubtree(
                new Tag(
                    array(
                        'id' => PHP_INT_MAX,
                    )
                ),
                new Tag(
                    array(
                        'id' => 40,
                    )
                )
            );
            $this->fail('First tag was found');
        } catch (NotFoundException $e) {
            // Do nothing
        }

        try {
            $this->tagsService->copySubtree(
                new Tag(
                    array(
                        'id' => 16,
                    )
                ),
                new Tag(
                    array(
                        'id' => PHP_INT_MAX,
                    )
                )
            );
            $this->fail('Second tag was found');
        } catch (NotFoundException $e) {
            // Do nothing
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::copySubtree
     * @depends testLoadTag
     */
    public function testCopySubtreeThrowsInvalidArgumentExceptionTagsAreSynonyms()
    {
        try {
            $this->tagsService->copySubtree(
                $this->tagsService->loadTag(95),
                $this->tagsService->loadTag(40)
            );
            $this->fail('First tag is a synonym');
        } catch (InvalidArgumentException $e) {
            // Do nothing
        }

        try {
            $this->tagsService->copySubtree(
                $this->tagsService->loadTag(16),
                $this->tagsService->loadTag(95)
            );
            $this->fail('Second tag is a synonym');
        } catch (InvalidArgumentException $e) {
            // Do nothing
        }
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::copySubtree
     * @depends testLoadTag
     */
    public function testCopySubtreeThrowsInvalidArgumentExceptionTargetTagBelowTag()
    {
        $this->tagsService->copySubtree(
            $this->tagsService->loadTag(7),
            $this->tagsService->loadTag(40)
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::copySubtree
     * @depends testLoadTag
     */
    public function testCopySubtreeThrowsInvalidArgumentExceptionTargetTagAlreadyParent()
    {
        $this->tagsService->copySubtree(
            $this->tagsService->loadTag(7),
            $this->tagsService->loadTag(8)
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::copySubtree
     */
    public function testCopySubtreeThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->copySubtree(
            new Tag(
                array(
                    'id' => 16,
                )
            ),
            new Tag(
                array(
                    'id' => 40,
                )
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::moveSubtree
     * @depends testLoadTag
     * @depends testLoadTagSynonyms
     */
    public function testMoveSubtree()
    {
        $tag = $this->tagsService->loadTag(16);
        $targetParentTag = $this->tagsService->loadTag(40);

        $movedTag = $this->tagsService->moveSubtree($tag, $targetParentTag);

        $this->assertInstanceOf(Tag::class, $movedTag);

        $this->assertPropertiesCorrect(
            array(
                'id' => $tag->id,
                'parentTagId' => $targetParentTag->id,
                'mainTagId' => $tag->mainTagId,
                'keyword' => $tag->keyword,
                'depth' => $targetParentTag->depth + 1,
                'pathString' => $targetParentTag->pathString . $tag->id . '/',
                'remoteId' => $tag->remoteId,
            ),
            $movedTag
        );

        $this->assertInstanceOf('\\DateTime', $movedTag->modificationDate);
        $this->assertGreaterThan($tag->modificationDate->getTimestamp(), $movedTag->modificationDate->getTimestamp());

        foreach ($this->tagsService->loadTagSynonyms($movedTag) as $synonym) {
            $this->assertEquals($targetParentTag->id, $synonym->parentTagId);
            $this->assertEquals($targetParentTag->depth + 1, $synonym->depth);
            $this->assertEquals($targetParentTag->pathString . $synonym->id . '/', $synonym->pathString);
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::moveSubtree
     */
    public function testMoveSubtreeThrowsNotFoundException()
    {
        try {
            $this->tagsService->moveSubtree(
                new Tag(
                    array(
                        'id' => PHP_INT_MAX,
                    )
                ),
                new Tag(
                    array(
                        'id' => 40,
                    )
                )
            );
            $this->fail('First tag was found');
        } catch (NotFoundException $e) {
            // Do nothing
        }

        try {
            $this->tagsService->moveSubtree(
                new Tag(
                    array(
                        'id' => 16,
                    )
                ),
                new Tag(
                    array(
                        'id' => PHP_INT_MAX,
                    )
                )
            );
            $this->fail('Second tag was found');
        } catch (NotFoundException $e) {
            // Do nothing
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::moveSubtree
     * @depends testLoadTag
     */
    public function testMoveSubtreeThrowsInvalidArgumentExceptionTagsAreSynonyms()
    {
        try {
            $this->tagsService->moveSubtree(
                $this->tagsService->loadTag(95),
                $this->tagsService->loadTag(40)
            );
            $this->fail('First tag is a synonym');
        } catch (InvalidArgumentException $e) {
            // Do nothing
        }

        try {
            $this->tagsService->moveSubtree(
                $this->tagsService->loadTag(16),
                $this->tagsService->loadTag(95)
            );
            $this->fail('Second tag is a synonym');
        } catch (InvalidArgumentException $e) {
            // Do nothing
        }
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::moveSubtree
     * @depends testLoadTag
     */
    public function testMoveSubtreeThrowsInvalidArgumentExceptionTargetTagBelowTag()
    {
        $this->tagsService->moveSubtree(
            $this->tagsService->loadTag(7),
            $this->tagsService->loadTag(40)
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::moveSubtree
     * @depends testLoadTag
     */
    public function testMoveSubtreeThrowsInvalidArgumentExceptionTargetTagAlreadyParent()
    {
        $this->tagsService->moveSubtree(
            $this->tagsService->loadTag(7),
            $this->tagsService->loadTag(8)
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::moveSubtree
     */
    public function testMoveSubtreeThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->moveSubtree(
            new Tag(
                array(
                    'id' => 16,
                )
            ),
            new Tag(
                array(
                    'id' => 40,
                )
            )
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::deleteTag
     * @depends testLoadTag
     * @depends testLoadTagSynonyms
     * @depends testLoadTagChildren
     */
    public function testDeleteTag()
    {
        $tag = $this->tagsService->loadTag(16);
        $tagSynonyms = $this->tagsService->loadTagSynonyms($tag);
        $tagChildren = $this->tagsService->loadTagChildren($tag);

        $this->tagsService->deleteTag($tag);

        try {
            $this->tagsService->loadTag($tag->id);
            $this->fail('Tag not deleted');
        } catch (NotFoundException $e) {
            // Do nothing
        }

        foreach ($tagSynonyms as $synonym) {
            try {
                $this->tagsService->loadTag($synonym->id);
                $this->fail('Synonym not deleted');
            } catch (NotFoundException $e) {
                // Do nothing
            }
        }

        foreach ($tagChildren as $child) {
            try {
                $this->tagsService->loadTag($child->id);
                $this->fail('Child not deleted');
            } catch (NotFoundException $e) {
                // Do nothing
            }
        }
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::deleteTag
     */
    public function testDeleteTagThrowsNotFoundException()
    {
        $this->tagsService->deleteTag(
            new Tag(
                array(
                    'id' => PHP_INT_MAX,
                )
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::deleteTag
     */
    public function testDeleteTagThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->deleteTag(
            new Tag(
                array(
                    'id' => 40,
                )
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \Netgen\TagsBundle\Core\Repository\TagsService::deleteTag
     */
    public function testDeleteTagThrowsUnauthorizedExceptionForSynonym()
    {
        $this->repository->setCurrentUser($this->getStubbedUser(10));
        $this->tagsService->deleteTag(
            new Tag(
                array(
                    'id' => 95,
                )
            )
        );
    }

    /**
     * Creates and returns a \DateTime object with received timestamp.
     *
     * @param int $timestamp
     *
     * @return \DateTime
     */
    protected function getDateTime($timestamp = null)
    {
        $timestamp = $timestamp ?: time();

        $dateTime = new DateTime();
        $dateTime->setTimestamp($timestamp);

        return $dateTime;
    }

    /**
     * Returns the path string of a synonym for main tag path string.
     *
     * @param mixed $synonymId
     * @param string $mainTagPathString
     *
     * @return string
     */
    protected function getSynonymPathString($synonymId, $mainTagPathString)
    {
        $pathStringElements = explode('/', trim($mainTagPathString, '/'));
        array_pop($pathStringElements);

        return (!empty($pathStringElements) ? '/' . implode('/', $pathStringElements) : '') . '/' . (int) $synonymId . '/';
    }
}
