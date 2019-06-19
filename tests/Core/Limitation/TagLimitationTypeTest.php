<?php

namespace Netgen\TagsBundle\Tests\Core\Limitation;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Limitation\Tests\Base;
use eZ\Publish\SPI\Persistence\Handler as SPIHandler;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\User\Limitation\TagLimitation;
use Netgen\TagsBundle\Core\Limitation\TagLimitationType;
use Netgen\TagsBundle\SPI\Persistence\Tags\Handler;
use Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo;
use RuntimeException;

class TagLimitationTypeTest extends Base
{
    /**
     * @var \Netgen\TagsBundle\SPI\Persistence\Tags\Handler|\PHPUnit\Framework\MockObject\MockObject
     */
    private $tagsHandlerMock;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler|\PHPUnit\Framework\MockObject\MockObject
     */
    private $persistenceHandlerMock;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User|\PHPUnit\Framework\MockObject\MockObject
     */
    private $userMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->persistenceHandlerMock = $this->createMock(SPIHandler::class);
        $this->userMock = $this->createMock(User::class);
        $this->tagsHandlerMock = $this->createMock(Handler::class);
    }

    protected function tearDown(): void
    {
        $this->tagsHandlerMock = null;
        parent::tearDown();
    }

    public function testConstruct(): TagLimitationType
    {
        return new TagLimitationType(
            $this->persistenceHandlerMock,
            $this->tagsHandlerMock
        );
    }

    public function providerForTestAcceptValue(): array
    {
        return [
            [new TagLimitation()],
            [new TagLimitation([])],
            [
                new TagLimitation(
                    [
                        'limitationValues' => [
                            '1',
                            '2',
                            '3',
                        ],
                    ]
                ),
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => [
                            1,
                            2,
                            3,
                        ],
                    ]
                ),
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => [
                            1,
                            '2',
                            '3',
                        ],
                    ]
                ),
            ],
        ];
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestAcceptValue
     */
    public function testAcceptValue(TagLimitation $limitation, TagLimitationType $limitationType): void
    {
        $limitationType->acceptValue($limitation);
    }

    public function providerForTestAcceptValueException(): array
    {
        return [
            [new ObjectStateLimitation()],
            [
                new TagLimitation(
                    [
                        'limitationValues' => true,
                    ]
                ),
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => [true],
                    ]
                ),
            ],
        ];
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestAcceptValueException
     */
    public function testAcceptValueException(Limitation $limitation, TagLimitationType $limitationType): void
    {
        $this->expectException(InvalidArgumentException::class);

        $limitationType->acceptValue($limitation);
    }

    public function providerForTestValidate(): array
    {
        return [
            [
                new TagLimitation(),
                0,
            ],
            [
                new TagLimitation(
                    []
                ),
                0,
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => [1, 2, 3],
                    ]
                ),
                0,
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => ['1', '2', '3'],
                    ]
                ),
                0,
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => ['1', 2, 3],
                    ]
                ),
                0,
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => [true],
                    ]
                ),
                1,
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => ['1', false],
                    ]
                ),
                1,
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => ['1', 2, false],
                    ]
                ),
                1,
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => [null, []],
                    ]
                ),
                2,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestValidate
     * @depends testConstruct
     */
    public function testValidate(TagLimitation $limitation, int $errorCount): void
    {
        if (is_array($limitation->limitationValues) && count($limitation->limitationValues) > 0) {
            foreach ($limitation->limitationValues as $key => $value) {
                if (is_int($value) || ctype_digit($value)) {
                    $this->tagsHandlerMock
                        ->expects(self::at($key))
                        ->method('loadTagInfo')
                        ->with($value)
                        ->willReturn(
                            new TagInfo(['id' => $value])
                        );
                } else {
                    $this->tagsHandlerMock
                        ->expects(self::at($key))
                        ->method('loadTagInfo')
                        ->with($value)
                        ->will(self::throwException(new NotFoundException('tag', $value)));
                }
            }
        } else {
            $this->tagsHandlerMock
                ->expects(self::never())
                ->method(self::anything());
        }

        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $validationErrors = $limitationType->validate($limitation);
        self::assertCount($errorCount, $validationErrors);
    }

    /**
     * @depends testConstruct
     */
    public function testBuildValue(TagLimitationType $limitationType): void
    {
        $expected = ['1', 2, '3'];
        $value = $limitationType->buildValue($expected);

        self::assertInstanceOf(TagLimitation::class, $value);
        self::assertIsArray($value->limitationValues);
        self::assertSame($expected, $value->limitationValues);
    }

    public function providerForTestEvaluate(): array
    {
        return [
            // Tag with no access
            [
                'limitation' => new TagLimitation(),
                'object' => new Tag(['id' => 1]),
                'expected' => false,
            ],
            // Tag with no access
            [
                'limitation' => new TagLimitation(['limitationValues' => [2]]),
                'object' => new Tag(['id' => 1]),
                'expected' => false,
            ],
            // Tag with access
            [
                'limitation' => new TagLimitation(['limitationValues' => [1]]),
                'object' => new Tag(['id' => 1]),
                'expected' => true,
            ],
        ];
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestEvaluate
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\User\Limitation\TagLimitation $limitation
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param mixed $expected
     * @param \Netgen\TagsBundle\Core\Limitation\TagLimitationType $limitationType
     */
    public function testEvaluate(TagLimitation $limitation, ValueObject $object, $expected, TagLimitationType $limitationType): void
    {
        $this->userMock->expects(self::never())->method(self::anything());

        $value = $limitationType->evaluate($limitation, $this->userMock, $object);

        self::assertIsBool($value);
        self::assertSame($expected, $value);
    }

    public function providerForTestEvaluateInvalidArgument(): array
    {
        return [
            // invalid limitation
            [
                'limitation' => new ObjectStateLimitation(),
                'object' => new Tag(),
            ],
            // invalid object
            [
                'limitation' => new TagLimitation(),
                'object' => new ObjectStateLimitation(),
            ],
        ];
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestEvaluateInvalidArgument
     */
    public function testEvaluateInvalidArgument(Limitation $limitation, ValueObject $object, TagLimitationType $limitationType): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->userMock->expects(self::never())->method(self::anything());

        $limitationType->evaluate($limitation, $this->userMock, $object);
    }

    /**
     * @depends testConstruct
     */
    public function testGetCriterionInvalidValue(TagLimitationType $limitationType): void
    {
        $this->expectException(RuntimeException::class);

        $limitationType->getCriterion(
            new TagLimitation([]),
            $this->userMock
        );
    }

    /**
     * @depends testConstruct
     */
    public function testGetCriterionSingleValue(TagLimitationType $limitationType): void
    {
        /** @var \Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId $criterion */
        $criterion = $limitationType->getCriterion(
            new TagLimitation(['limitationValues' => [1]]),
            $this->userMock
        );

        self::assertInstanceOf(TagId::class, $criterion);
        self::assertIsArray($criterion->value);
        self::assertIsString($criterion->operator);
        self::assertSame(Operator::EQ, $criterion->operator);
        self::assertSame([1], $criterion->value);
    }

    /**
     * @depends testConstruct
     */
    public function testGetCriterionMultipleValues(TagLimitationType $limitationType): void
    {
        /** @var \Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId $criterion */
        $criterion = $limitationType->getCriterion(
            new TagLimitation(['limitationValues' => [1, 2]]),
            $this->userMock
        );

        self::assertInstanceOf(TagId::class, $criterion);
        self::assertIsArray($criterion->value);
        self::assertIsString($criterion->operator);
        self::assertSame(Operator::IN, $criterion->operator);
        self::assertSame([1, 2], $criterion->value);
    }

    /**
     * @depends testConstruct
     */
    public function testValueSchema(TagLimitationType $limitationType): void
    {
        $this->expectException(NotImplementedException::class);

        $limitationType->valueSchema();
    }
}
