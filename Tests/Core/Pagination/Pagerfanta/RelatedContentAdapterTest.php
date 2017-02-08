<?php

namespace Netgen\TagsBundle\Tests\Core\Pagination\Pagerfanta;

use eZ\Publish\Core\Repository\Values\Content\Content;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter;
use PHPUnit_Framework_TestCase;

class RelatedContentAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tagsService;

    /**
     * Sets up the test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->tagsService = $this->getMock(TagsService::class);
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter::__construct
     * @covers \Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter::setTag
     * @covers \Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter::getNbResults
     */
    public function testGetNbResults()
    {
        $nbResults = 4;

        $tag = new Tag(
            array(
                'id' => 42,
            )
        );

        $this->tagsService
            ->expects($this->once())
            ->method('getRelatedContentCount')
            ->with($this->equalTo($tag))
            ->will($this->returnValue($nbResults));

        $adapter = $this->getAdapter($tag, $this->tagsService);
        $this->assertSame($nbResults, $adapter->getNbResults());

        // Running a 2nd time to ensure TagsService::getRelatedContentCount() is called only once.
        $this->assertSame($nbResults, $adapter->getNbResults());
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter::__construct
     * @covers \Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter::setTag
     * @covers \Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter::getNbResults
     */
    public function testGetNbResultsWithoutTag()
    {
        $this->tagsService
            ->expects($this->never())
            ->method('getRelatedContentCount');

        $adapter = new RelatedContentAdapter($this->tagsService);
        $this->assertSame(0, $adapter->getNbResults());
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter::__construct
     * @covers \Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter::setTag
     * @covers \Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter::getSlice
     */
    public function testGetSlice()
    {
        $offset = 2;
        $limit = 2;
        $nbResults = 5;

        $tag = new Tag(
            array(
                'id' => 42,
            )
        );

        $relatedContent = array(
            new Content(
                array(
                    'internalFields' => array(),
                )
            ),
            new Content(
                array(
                    'internalFields' => array(),
                )
            ),
        );

        $this->tagsService
            ->expects($this->once())
            ->method('getRelatedContentCount')
            ->with($this->equalTo($tag))
            ->will($this->returnValue($nbResults));

        $this
            ->tagsService
            ->expects($this->once())
            ->method('getRelatedContent')
            ->with(
                $this->equalTo($tag),
                $this->equalTo($offset),
                $this->equalTo($limit)
            )
            ->will(
                $this->returnValue($relatedContent)
            );

        $adapter = $this->getAdapter($tag, $this->tagsService);

        $this->assertSame($relatedContent, $adapter->getSlice($offset, $limit));
        $this->assertSame($nbResults, $adapter->getNbResults());
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter::__construct
     * @covers \Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter::setTag
     * @covers \Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter::getSlice
     */
    public function testGetSliceWithoutTag()
    {
        $this->tagsService
            ->expects($this->never())
            ->method('getRelatedContentCount');

        $this
            ->tagsService
            ->expects($this->never())
            ->method('getRelatedContent');

        $adapter = new RelatedContentAdapter($this->tagsService);

        $this->assertSame(array(), $adapter->getSlice(2, 2));
    }

    /**
     * Returns the adapter to test.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     *
     * @return \Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter
     */
    protected function getAdapter(Tag $tag, TagsService $tagsService)
    {
        $adapter = new RelatedContentAdapter($tagsService);
        $adapter->setTag($tag);

        return $adapter;
    }
}
