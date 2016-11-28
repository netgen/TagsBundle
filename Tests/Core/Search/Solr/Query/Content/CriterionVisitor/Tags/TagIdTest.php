<?php

namespace Netgen\TagsBundle\Tests\Core\Search\Solr\Query\Content\CriterionVisitor\Tags;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationId;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\TestCase;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion;
use Netgen\TagsBundle\Core\Search\Solr\Query;

class TagIdTest extends TestCase
{
    /**
     * @var \eZ\Publish\Core\Search\Common\FieldNameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldNameResolver;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeHandler;

    /**
     * @var \Netgen\TagsBundle\Core\Search\Solr\Query\Content\CriterionVisitor\Tags\TagId
     */
    protected $visitor;

    public function setUp()
    {
        $this->fieldNameResolver = $this
            ->getMockBuilder('eZ\\Publish\\Core\\Search\\Common\\FieldNameResolver')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentTypeHandler = $this
            ->getMockBuilder('eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler')
            ->disableOriginalConstructor()
            ->getMock();

        $this->visitor = new Query\Content\CriterionVisitor\Tags\TagId(
            $this->fieldNameResolver,
            $this->contentTypeHandler,
            'eztags',
            'tag_ids'
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Content\CriterionVisitor\Tags\TagId::canVisit
     */
    public function testCanVisit()
    {
        $criterion = new Criterion\TagId(array(42, 43));
        self::assertTrue($this->visitor->canVisit($criterion));
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Content\CriterionVisitor\Tags\TagId::canVisit
     */
    public function testCanVisitReturnsFalse()
    {
        $criterion = new LocationId(array(42, 43));
        self::assertFalse($this->visitor->canVisit($criterion));
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Content\CriterionVisitor\Tags\TagId::visit
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Content\CriterionVisitor\Tags::getTargetFieldNames
     */
    public function testVisit()
    {
        $criterion = new Criterion\TagId(array(42, 43), 'tags_field');

        $this->fieldNameResolver
            ->expects($this->once())
            ->method('getFieldNames')
            ->with(
                $this->equalTo($criterion),
                $this->equalTo('tags_field'),
                'eztags',
                'tag_ids'
            )
            ->will($this->returnValue(array('tags_field_s', 'tags_field2_s')));

        $this->contentTypeHandler
            ->expects($this->never())
            ->method('getSearchableFieldMap');

        $this->assertEquals(
            '(tags_field_s:42 OR tags_field2_s:42 OR tags_field_s:43 OR tags_field2_s:43)',
            $this->visitor->visit($criterion)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Content\CriterionVisitor\Tags\TagId::visit
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Content\CriterionVisitor\Tags::getTargetFieldNames
     */
    public function testVisitWithoutTarget()
    {
        $criterion = new Criterion\TagId(array(42, 43));

        $this->contentTypeHandler
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    array(
                        'news' => array(
                            'tags_field' => array(
                                'field_type_identifier' => 'eztags',
                            ),
                        ),
                        'article' => array(
                            'tags_field2' => array(
                                'field_type_identifier' => 'eztags',
                            ),
                        ),
                    )
                )
            );

        $this->fieldNameResolver
            ->expects($this->at(0))
            ->method('getFieldNames')
            ->with(
                $this->equalTo($criterion),
                $this->equalTo('tags_field'),
                'eztags',
                'tag_ids'
            )
            ->will($this->returnValue(array('news_tags_field_s')));

        $this->fieldNameResolver
            ->expects($this->at(1))
            ->method('getFieldNames')
            ->with(
                $this->equalTo($criterion),
                $this->equalTo('tags_field2'),
                'eztags',
                'tag_ids'
            )
            ->will($this->returnValue(array('article_tags_field2_s')));

        $this->assertEquals(
            '(news_tags_field_s:42 OR article_tags_field2_s:42 OR news_tags_field_s:43 OR article_tags_field2_s:43)',
            $this->visitor->visit($criterion)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Content\CriterionVisitor\Tags\TagId::visit
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Content\CriterionVisitor\Tags::getTargetFieldNames
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testVisitThrowsInvalidArgumentException()
    {
        $criterion = new Criterion\TagId(array(42, 43), 'tags_field');

        $this->fieldNameResolver
            ->expects($this->once())
            ->method('getFieldNames')
            ->with(
                $this->equalTo($criterion),
                $this->equalTo('tags_field'),
                'eztags',
                'tag_ids'
            )
            ->will($this->returnValue(array()));

        $this->visitor->visit($criterion);
    }
}
