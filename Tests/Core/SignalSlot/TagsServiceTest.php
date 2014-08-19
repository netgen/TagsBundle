<?php

namespace Netgen\TagsBundle\Tests\Core\SignalSlot;

use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\AddSynonymSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\ConvertToSynonymSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\CopySubtreeSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\CreateTagSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\DeleteTagSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\MergeTagsSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\MoveSubtreeSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\UpdateTagSignal;
use Netgen\TagsBundle\Core\SignalSlot\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use eZ\Publish\Core\Repository\Values\Content\Content;
use PHPUnit_Framework_TestCase;

class TagsServiceTest extends PHPUnit_Framework_TestCase
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
     * Sets up the test
     */
    protected function setUp()
    {
        parent::setUp();

        $this->tagsService = $this->getMock( 'Netgen\TagsBundle\API\Repository\TagsService' );
        $this->signalDispatcher = $this->getMock( 'eZ\Publish\Core\SignalSlot\SignalDispatcher' );
    }

    /**
     * Returns signal slot service under test
     *
     * @return \Netgen\TagsBundle\Core\SignalSlot\TagsService
     */
    protected function getSignalSlotService()
    {
        return new TagsService( $this->tagsService, $this->signalDispatcher );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::loadTag
     */
    public function testLoadTag()
    {
        $this->tagsService
            ->expects( $this->once() )
            ->method( 'loadTag' )
            ->with( $this->equalTo( 42 ) )
            ->will(
                $this->returnValue(
                    new Tag( array( 'id' => 42 ) )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $tag = $signalSlotService->loadTag( 42 );

        $this->assertInstanceOf( 'Netgen\TagsBundle\API\Repository\Values\Tags\Tag', $tag );
        $this->assertEquals( 42, $tag->id );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::loadTagByRemoteId
     */
    public function testLoadTagByRemoteId()
    {
        $this->tagsService
            ->expects( $this->once() )
            ->method( 'loadTagByRemoteId' )
            ->with( $this->equalTo( '12345' ) )
            ->will(
                $this->returnValue(
                    new Tag( array( 'remoteId' => '12345' ) )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $tag = $signalSlotService->loadTagByRemoteId( '12345' );

        $this->assertInstanceOf( 'Netgen\TagsBundle\API\Repository\Values\Tags\Tag', $tag );
        $this->assertEquals( '12345', $tag->remoteId );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::loadTagByUrl
     */
    public function testLoadTagByUrl()
    {
        $this->tagsService
            ->expects( $this->once() )
            ->method( 'loadTagByUrl' )
            ->with( $this->equalTo( 'Netgen/TagsBundle' ) )
            ->will(
                $this->returnValue(
                    new Tag( array( 'keyword' => 'TagsBundle' ) )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $tag = $signalSlotService->loadTagByUrl( 'Netgen/TagsBundle' );

        $this->assertInstanceOf( 'Netgen\TagsBundle\API\Repository\Values\Tags\Tag', $tag );
        $this->assertEquals( 'TagsBundle', $tag->keyword );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::loadTagChildren
     */
    public function testLoadTagChildren()
    {
        $this->tagsService
            ->expects( $this->once() )
            ->method( 'loadTagChildren' )
            ->with( $this->equalTo( new Tag( array( 'id' => 42 ) ) ) )
            ->will(
                $this->returnValue(
                    array(
                        new Tag( array( 'parentTagId' => 42 ) ),
                        new Tag( array( 'parentTagId' => 42 ) )
                    )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $tags = $signalSlotService->loadTagChildren( new Tag( array( 'id' => 42 ) ) );

        $this->assertCount( 2, $tags );

        foreach ( $tags as $tag )
        {
            $this->assertInstanceOf( 'Netgen\TagsBundle\API\Repository\Values\Tags\Tag', $tag );
            $this->assertEquals( 42, $tag->parentTagId );
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::getTagChildrenCount
     */
    public function testGetTagChildrenCount()
    {
        $this->tagsService
            ->expects( $this->once() )
            ->method( 'getTagChildrenCount' )
            ->with( $this->equalTo( new Tag( array( 'id' => 42 ) ) ) )
            ->will( $this->returnValue( 2 ) );

        $signalSlotService = $this->getSignalSlotService();
        $tagsCount = $signalSlotService->getTagChildrenCount( new Tag( array( 'id' => 42 ) ) );

        $this->assertEquals( 2, $tagsCount );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::loadTagsByKeyword
     */
    public function testLoadTagsByKeyword()
    {
        $this->tagsService
            ->expects( $this->once() )
            ->method( 'loadTagsByKeyword' )
            ->with( $this->equalTo( 'netgen' ) )
            ->will(
                $this->returnValue(
                    array(
                        new Tag( array( 'keyword' => 'netgen' ) ),
                        new Tag( array( 'keyword' => 'netgen' ) )
                    )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $tags = $signalSlotService->loadTagsByKeyword( 'netgen' );

        $this->assertCount( 2, $tags );

        foreach ( $tags as $tag )
        {
            $this->assertInstanceOf( 'Netgen\TagsBundle\API\Repository\Values\Tags\Tag', $tag );
            $this->assertEquals( 'netgen', $tag->keyword );
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::getTagsByKeywordCount
     */
    public function testGetTagsByKeywordCount()
    {
        $this->tagsService
            ->expects( $this->once() )
            ->method( 'getTagsByKeywordCount' )
            ->with( $this->equalTo( 'netgen' ) )
            ->will( $this->returnValue( 2 ) );

        $signalSlotService = $this->getSignalSlotService();
        $tagsCount = $signalSlotService->getTagsByKeywordCount( 'netgen' );

        $this->assertEquals( 2, $tagsCount );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::loadTagSynonyms
     */
    public function testLoadTagSynonyms()
    {
        $this->tagsService
            ->expects( $this->once() )
            ->method( 'loadTagSynonyms' )
            ->with( $this->equalTo( new Tag( array( 'id' => 42 ) ) ) )
            ->will(
                $this->returnValue(
                    array(
                        new Tag( array( 'mainTagId' => 42 ) ),
                        new Tag( array( 'mainTagId' => 42 ) )
                    )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $tags = $signalSlotService->loadTagSynonyms( new Tag( array( 'id' => 42 ) ) );

        $this->assertCount( 2, $tags );

        foreach ( $tags as $tag )
        {
            $this->assertInstanceOf( 'Netgen\TagsBundle\API\Repository\Values\Tags\Tag', $tag );
            $this->assertEquals( 42, $tag->mainTagId );
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::getTagSynonymCount
     */
    public function testGetTagSynonymCount()
    {
        $this->tagsService
            ->expects( $this->once() )
            ->method( 'getTagSynonymCount' )
            ->with( $this->equalTo( new Tag( array( 'id' => 42 ) ) ) )
            ->will( $this->returnValue( 2 ) );

        $signalSlotService = $this->getSignalSlotService();
        $tagsCount = $signalSlotService->getTagSynonymCount( new Tag( array( 'id' => 42 ) ) );

        $this->assertEquals( 2, $tagsCount );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::getRelatedContent
     */
    public function testGetRelatedContent()
    {
        $this->tagsService
            ->expects( $this->once() )
            ->method( 'getRelatedContent' )
            ->with( $this->equalTo( new Tag( array( 'id' => 42 ) ) ) )
            ->will(
                $this->returnValue(
                    array(
                        new Content(),
                        new Content()
                    )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $content = $signalSlotService->getRelatedContent( new Tag( array( 'id' => 42 ) ) );

        $this->assertCount( 2, $content );

        foreach ( $content as $contentItem )
        {
            $this->assertInstanceOf( 'eZ\Publish\API\Repository\Values\Content\Content', $contentItem );
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::getRelatedContentCount
     */
    public function testGetRelatedContentCount()
    {
        $this->tagsService
            ->expects( $this->once() )
            ->method( 'getRelatedContentCount' )
            ->with( $this->equalTo( new Tag( array( 'id' => 42 ) ) ) )
            ->will( $this->returnValue( 2 ) );

        $signalSlotService = $this->getSignalSlotService();
        $contentCount = $signalSlotService->getRelatedContentCount( new Tag( array( 'id' => 42 ) ) );

        $this->assertEquals( 2, $contentCount );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::createTag
     */
    public function testCreateTag()
    {
        $tagCreateStruct = new TagCreateStruct();
        $tagCreateStruct->parentTagId = '42';
        $tagCreateStruct->keyword = 'netgen';

        $this->tagsService
            ->expects( $this->once() )
            ->method( 'createTag' )
            ->with( $this->equalTo( $tagCreateStruct ) )
            ->will(
                $this->returnValue(
                    new Tag(
                        array(
                            'id' => 24,
                            'parentTagId' => 42,
                            'keyword' => 'netgen'
                        )
                    )
                )
            )
        ;

        $this->signalDispatcher
            ->expects( $this->once() )
            ->method( 'emit' )
            ->with(
                $this->equalTo(
                    new CreateTagSignal(
                        array(
                            'tagId' => 24,
                            'parentTagId' => 42,
                            'keyword' => 'netgen'
                        )
                    )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $createdTag = $signalSlotService->createTag( $tagCreateStruct );

        $this->assertInstanceOf( 'Netgen\TagsBundle\API\Repository\Values\Tags\Tag', $createdTag );

        $this->assertEquals( 24, $createdTag->id );
        $this->assertEquals( 42, $createdTag->parentTagId );
        $this->assertEquals( 'netgen', $createdTag->keyword );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::updateTag
     */
    public function testUpdateTag()
    {
        $tagUpdateStruct = new TagUpdateStruct();
        $tagUpdateStruct->keyword = 'netgen';

        $tag = new Tag(
            array(
                'id' => 42,
                'keyword' => 'ez',
                'remoteId' => '123456'
            )
        );

        $this->tagsService
            ->expects( $this->once() )
            ->method( 'updateTag' )
            ->with(
                $this->equalTo( $tag ),
                $this->equalTo( $tagUpdateStruct )
            )
            ->will(
                $this->returnValue(
                    new Tag(
                        array(
                            'id' => 42,
                            'keyword' => 'netgen',
                            'remoteId' => 123456
                        )
                    )
                )
            )
        ;

        $this->signalDispatcher
            ->expects( $this->once() )
            ->method( 'emit' )
            ->with(
                $this->equalTo(
                    new UpdateTagSignal(
                        array(
                            'tagId' => 42,
                            'keyword' => 'netgen',
                            'remoteId' => '123456'
                        )
                    )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $updatedTag = $signalSlotService->updateTag( $tag, $tagUpdateStruct );

        $this->assertInstanceOf( 'Netgen\TagsBundle\API\Repository\Values\Tags\Tag', $updatedTag );

        $this->assertEquals( 42, $updatedTag->id );
        $this->assertEquals( 'netgen', $updatedTag->keyword );
        $this->assertEquals( '123456', $updatedTag->remoteId );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::addSynonym
     */
    public function testAddSynonym()
    {
        $tag = new Tag(
            array(
                'id' => 42,
                'keyword' => 'netgen'
            )
        );

        $this->tagsService
            ->expects( $this->once() )
            ->method( 'addSynonym' )
            ->with(
                $this->equalTo( $tag ),
                $this->equalTo( 'netgenlabs' )
            )
            ->will(
                $this->returnValue(
                    new Tag(
                        array(
                            'id' => 24,
                            'keyword' => 'netgenlabs',
                            'mainTagId' => 42
                        )
                    )
                )
            )
        ;

        $this->signalDispatcher
            ->expects( $this->once() )
            ->method( 'emit' )
            ->with(
                $this->equalTo(
                    new AddSynonymSignal(
                        array(
                            'tagId' => 24,
                            'mainTagId' => 42,
                            'keyword' => 'netgenlabs'
                        )
                    )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $synonym = $signalSlotService->addSynonym( $tag, 'netgenlabs' );

        $this->assertInstanceOf( 'Netgen\TagsBundle\API\Repository\Values\Tags\Tag', $synonym );

        $this->assertEquals( 24, $synonym->id );
        $this->assertEquals( 42, $synonym->mainTagId );
        $this->assertEquals( 'netgenlabs', $synonym->keyword );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::convertToSynonym
     */
    public function testConvertToSynonym()
    {
        $tag = new Tag(
            array(
                'id' => 42
            )
        );

        $mainTag = new Tag(
            array(
                'id' => 24
            )
        );

        $this->tagsService
            ->expects( $this->once() )
            ->method( 'convertToSynonym' )
            ->with(
                $this->equalTo( $tag ),
                $this->equalTo( $mainTag )
            )
            ->will(
                $this->returnValue(
                    new Tag(
                        array(
                            'id' => 42,
                            'mainTagId' => 24
                        )
                    )
                )
            )
        ;

        $this->signalDispatcher
            ->expects( $this->once() )
            ->method( 'emit' )
            ->with(
                $this->equalTo(
                    new ConvertToSynonymSignal(
                        array(
                            'tagId' => 42,
                            'mainTagId' => 24
                        )
                    )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $synonym = $signalSlotService->convertToSynonym( $tag, $mainTag );

        $this->assertInstanceOf( 'Netgen\TagsBundle\API\Repository\Values\Tags\Tag', $synonym );

        $this->assertEquals( 42, $synonym->id );
        $this->assertEquals( 24, $synonym->mainTagId );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::mergeTags
     */
    public function testMergeTags()
    {
        $tag = new Tag(
            array(
                'id' => 42
            )
        );

        $targetTag = new Tag(
            array(
                'id' => 24
            )
        );

        $this->tagsService
            ->expects( $this->once() )
            ->method( 'mergeTags' )
            ->with(
                $this->equalTo( $tag ),
                $this->equalTo( $targetTag )
            );

        $this->signalDispatcher
            ->expects( $this->once() )
            ->method( 'emit' )
            ->with(
                $this->equalTo(
                    new MergeTagsSignal(
                        array(
                            'tagId' => 42,
                            'targetTagId' => 24
                        )
                    )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $signalSlotService->mergeTags( $tag, $targetTag );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::copySubtree
     */
    public function testCopySubtree()
    {
        $tag = new Tag(
            array(
                'id' => 24,
                'keyword' => 'netgen'
            )
        );

        $targetTag = new Tag(
            array(
                'id' => 25
            )
        );

        $this->tagsService
            ->expects( $this->once() )
            ->method( 'copySubtree' )
            ->with(
                $this->equalTo( $tag ),
                $this->equalTo( $targetTag )
            )
            ->will(
                $this->returnValue(
                    new Tag(
                        array(
                            'id' => 42,
                            'parentTagId' => 25,
                            'keyword' => 'netgen'
                        )
                    )
                )
            )
        ;

        $this->signalDispatcher
            ->expects( $this->once() )
            ->method( 'emit' )
            ->with(
                $this->equalTo(
                    new CopySubtreeSignal(
                        array(
                            'sourceTagId' => 24,
                            'targetParentTagId' => 25,
                            'newTagId' => 42
                        )
                    )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $copiedTag = $signalSlotService->copySubtree( $tag, $targetTag );

        $this->assertInstanceOf( 'Netgen\TagsBundle\API\Repository\Values\Tags\Tag', $copiedTag );

        $this->assertEquals( 42, $copiedTag->id );
        $this->assertEquals( 25, $copiedTag->parentTagId );
        $this->assertEquals( 'netgen', $copiedTag->keyword );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::moveSubtree
     */
    public function testMoveSubtree()
    {
        $tag = new Tag(
            array(
                'id' => 24
            )
        );

        $targetTag = new Tag(
            array(
                'id' => 25
            )
        );

        $this->tagsService
            ->expects( $this->once() )
            ->method( 'moveSubtree' )
            ->with(
                $this->equalTo( $tag ),
                $this->equalTo( $targetTag )
            )
            ->will(
                $this->returnValue(
                    new Tag(
                        array(
                            'id' => 24,
                            'parentTagId' => 25
                        )
                    )
                )
            )
        ;

        $this->signalDispatcher
            ->expects( $this->once() )
            ->method( 'emit' )
            ->with(
                $this->equalTo(
                    new MoveSubtreeSignal(
                        array(
                            'sourceTagId' => 24,
                            'targetParentTagId' => 25
                        )
                    )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $movedTag = $signalSlotService->moveSubtree( $tag, $targetTag );

        $this->assertInstanceOf( 'Netgen\TagsBundle\API\Repository\Values\Tags\Tag', $movedTag );

        $this->assertEquals( 24, $movedTag->id );
        $this->assertEquals( 25, $movedTag->parentTagId );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::deleteTag
     */
    public function testDeleteTag()
    {
        $tag = new Tag(
            array(
                'id' => 42
            )
        );

        $this->tagsService
            ->expects( $this->once() )
            ->method( 'deleteTag' )
            ->with(
                $this->equalTo( $tag )
            );

        $this->signalDispatcher
            ->expects( $this->once() )
            ->method( 'emit' )
            ->with(
                $this->equalTo(
                    new DeleteTagSignal(
                        array(
                            'tagId' => 42
                        )
                    )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $signalSlotService->deleteTag( $tag );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::newTagCreateStruct
     */
    public function testNewTagCreateStruct()
    {
        $this->tagsService
            ->expects( $this->once() )
            ->method( 'newTagCreateStruct' )
            ->with( $this->equalTo( 42 ), $this->equalTo( 'netgen' ) )
            ->will(
                $this->returnValue(
                    new TagCreateStruct( array( 'parentTagId' => 42, 'keyword' => 'netgen' ) )
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $tagCreateStruct = $signalSlotService->newTagCreateStruct( 42, 'netgen' );

        $this->assertInstanceOf( 'Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct', $tagCreateStruct );
        $this->assertEquals( 42, $tagCreateStruct->parentTagId );
        $this->assertEquals( 'netgen', $tagCreateStruct->keyword );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\SignalSlot\TagsService::newTagUpdateStruct
     */
    public function testNewTagUpdateStruct()
    {
        $this->tagsService
            ->expects( $this->once() )
            ->method( 'newTagUpdateStruct' )
            ->will(
                $this->returnValue(
                    new TagUpdateStruct()
                )
            )
        ;

        $signalSlotService = $this->getSignalSlotService();
        $tagUpdateStruct = $signalSlotService->newTagUpdateStruct();

        $this->assertInstanceOf( 'Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct', $tagUpdateStruct );
    }
}
