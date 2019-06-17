<?php

namespace Netgen\TagsBundle\Tests\Core\Search\Solr\Query\Common\CriterionVisitor\Tags;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationId;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use eZ\Publish\Core\Search\Common\FieldValueMapper\MultipleIntegerMapper;
use eZ\Publish\SPI\Search\FieldType;
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
     * @var \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags\TagId
     */
    protected $visitor;

    public function setUp(): void
    {
        $this->fieldNameResolver = $this->createMock(FieldNameResolver::class);

        $this->contentTypeHandler = $this->createMock(Handler::class);

        $this->visitor = new Query\Common\CriterionVisitor\Tags\TagId(
            $this->fieldNameResolver,
            new MultipleIntegerMapper(),
            $this->contentTypeHandler,
            'eztags',
            'tag_ids'
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags\TagId::canVisit
     */
    public function testCanVisit(): void
    {
        $criterion = new Criterion\TagId([42, 43]);
        self::assertTrue($this->visitor->canVisit($criterion));
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags\TagId::canVisit
     */
    public function testCanVisitReturnsFalse(): void
    {
        $criterion = new LocationId([42, 43]);
        self::assertFalse($this->visitor->canVisit($criterion));
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags::getSearchFields
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags\TagId::visit
     */
    public function testVisit(): void
    {
        $criterion = new Criterion\TagId([42, 43], 'tags_field');

        $this->fieldNameResolver
            ->expects(self::once())
            ->method('getFieldTypes')
            ->with(
                self::equalTo($criterion),
                self::equalTo('tags_field'),
                'eztags',
                'tag_ids'
            )
            ->willReturn(
                [
                    'tags_field_s' => new FieldType\MultipleIntegerField(),
                    'tags_field2_s' => new FieldType\MultipleIntegerField(),
                ]
            );

        $this->contentTypeHandler
            ->expects(self::never())
            ->method('getSearchableFieldMap');

        self::assertSame(
            '(tags_field_s:"42" OR tags_field_s:"43" OR tags_field2_s:"42" OR tags_field2_s:"43")',
            $this->visitor->visit($criterion)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags::getSearchFields
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags\TagId::visit
     */
    public function testVisitWithoutTarget(): void
    {
        $criterion = new Criterion\TagId([42, 43]);

        $this->contentTypeHandler
            ->expects(self::once())
            ->method('getSearchableFieldMap')
            ->willReturn(
                [
                    'news' => [
                        'tags_field' => [
                            'field_type_identifier' => 'eztags',
                        ],
                    ],
                    'article' => [
                        'tags_field2' => [
                            'field_type_identifier' => 'eztags',
                        ],
                    ],
                ]
            );

        $this->fieldNameResolver
            ->expects(self::at(0))
            ->method('getFieldTypes')
            ->with(
                self::equalTo($criterion),
                self::equalTo('tags_field'),
                'eztags',
                'tag_ids'
            )
            ->willReturn(
                [
                    'news_tags_field_s' => new FieldType\MultipleIntegerField(),
                ]
            );

        $this->fieldNameResolver
            ->expects(self::at(1))
            ->method('getFieldTypes')
            ->with(
                self::equalTo($criterion),
                self::equalTo('tags_field2'),
                'eztags',
                'tag_ids'
            )
            ->willReturn(
                [
                    'article_tags_field2_s' => new FieldType\MultipleIntegerField(),
                ]
            );

        self::assertSame(
            '(news_tags_field_s:"42" OR news_tags_field_s:"43" OR article_tags_field2_s:"42" OR article_tags_field2_s:"43")',
            $this->visitor->visit($criterion)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags::getSearchFields
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags\TagId::visit
     */
    public function testVisitThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $criterion = new Criterion\TagId([42, 43], 'tags_field');

        $this->fieldNameResolver
            ->expects(self::once())
            ->method('getFieldTypes')
            ->with(
                self::equalTo($criterion),
                self::equalTo('tags_field'),
                'eztags',
                'tag_ids'
            )
            ->willReturn([]);

        $this->visitor->visit($criterion);
    }
}
