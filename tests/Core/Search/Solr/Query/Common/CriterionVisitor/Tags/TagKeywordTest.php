<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Core\Search\Solr\Query\Common\CriterionVisitor\Tags;

use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LocationId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Search\FieldType;
use Ibexa\Core\Persistence\Legacy\Content\Type\Handler;
use Ibexa\Core\Search\Common\FieldNameResolver;
use Ibexa\Core\Search\Common\FieldValueMapper\MultipleStringMapper;
use Ibexa\Tests\Solr\Search\TestCase;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion;
use Netgen\TagsBundle\Core\Search\Solr\Query;

final class TagKeywordTest extends TestCase
{
    /**
     * @var \Ibexa\Core\Search\Common\FieldNameResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fieldNameResolver;

    /**
     * @var \Ibexa\Contracts\Core\Persistence\Content\Type\Handler|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contentTypeHandler;

    /**
     * @var \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags\TagKeyword
     */
    private $visitor;

    protected function setUp(): void
    {
        $this->fieldNameResolver = $this->createMock(FieldNameResolver::class);

        $this->contentTypeHandler = $this->createMock(Handler::class);

        $this->visitor = new Query\Common\CriterionVisitor\Tags\TagKeyword(
            $this->fieldNameResolver,
            new MultipleStringMapper(),
            $this->contentTypeHandler,
            'eztags',
            'tag_keywords'
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags\TagKeyword::canVisit
     */
    public function testCanVisit(): void
    {
        $criterion = new Criterion\TagKeyword(Operator::IN, ['tag1', 'tag2']);
        self::assertTrue($this->visitor->canVisit($criterion));
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags\TagKeyword::canVisit
     */
    public function testCanVisitReturnsFalse(): void
    {
        $criterion = new LocationId([42, 24]);
        self::assertFalse($this->visitor->canVisit($criterion));
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags::getSearchFields
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags\TagKeyword::visit
     */
    public function testVisit(): void
    {
        $criterion = new Criterion\TagKeyword(Operator::IN, ['tag1', 'tag2'], 'tags_field');

        $this->fieldNameResolver
            ->expects(self::once())
            ->method('getFieldTypes')
            ->with(
                self::identicalTo($criterion),
                self::identicalTo('tags_field'),
                'eztags',
                'tag_keywords'
            )
            ->willReturn(
                [
                    'tags_field_s' => new FieldType\MultipleStringField(),
                    'tags_field2_s' => new FieldType\MultipleStringField(),
                ]
            );

        $this->contentTypeHandler
            ->expects(self::never())
            ->method('getSearchableFieldMap');

        self::assertSame(
            '(tags_field_s:"tag1" OR tags_field_s:"tag2" OR tags_field2_s:"tag1" OR tags_field2_s:"tag2")',
            $this->visitor->visit($criterion)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags::getSearchFields
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags\TagKeyword::visit
     */
    public function testVisitWithoutTarget(): void
    {
        $criterion = new Criterion\TagKeyword(Operator::IN, ['tag1', 'tag2']);

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
                self::identicalTo($criterion),
                self::identicalTo('tags_field'),
                'eztags',
                'tag_keywords'
            )
            ->willReturn(
                [
                    'news_tags_field_s' => new FieldType\MultipleStringField(),
                ]
            );

        $this->fieldNameResolver
            ->expects(self::at(1))
            ->method('getFieldTypes')
            ->with(
                self::identicalTo($criterion),
                self::identicalTo('tags_field2'),
                'eztags',
                'tag_keywords'
            )
            ->willReturn(
                [
                    'article_tags_field2_s' => new FieldType\MultipleStringField(),
                ]
            );

        self::assertSame(
            '(news_tags_field_s:"tag1" OR news_tags_field_s:"tag2" OR article_tags_field2_s:"tag1" OR article_tags_field2_s:"tag2")',
            $this->visitor->visit($criterion)
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags::getSearchFields
     * @covers \Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags\TagKeyword::visit
     */
    public function testVisitThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $criterion = new Criterion\TagKeyword(Operator::IN, ['tag1', 'tag2'], 'tags_field');

        $this->fieldNameResolver
            ->expects(self::once())
            ->method('getFieldTypes')
            ->with(
                self::identicalTo($criterion),
                self::identicalTo('tags_field'),
                'eztags',
                'tag_keywords'
            )
            ->willReturn([]);

        $this->visitor->visit($criterion);
    }
}
