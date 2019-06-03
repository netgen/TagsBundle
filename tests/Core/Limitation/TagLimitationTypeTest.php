<?php

namespace Netgen\TagsBundle\Tests\Core\Limitation;

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

class TagLimitationTypeTest extends Base
{
    /**
     * @var \Netgen\TagsBundle\SPI\Persistence\Tags\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tagsHandlerMock;
    /**
     * @var \eZ\Publish\SPI\Persistence\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistenceHandlerMock;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userMock;

    /**
     * Setup Tags Handler mock.
     */
    public function setUp()
    {
        parent::setUp();

        $this->persistenceHandlerMock = $this->createMock(SPIHandler::class);
        $this->userMock = $this->createMock(User::class);
        $this->tagsHandlerMock = $this->createMock(Handler::class);
    }

    /**
     * Tear down Location Handler mock.
     */
    public function tearDown()
    {
        $this->tagsHandlerMock = null;
        parent::tearDown();
    }

    /**
     * @return \Netgen\TagsBundle\Core\Limitation\TagLimitationType
     */
    public function testConstruct()
    {
        return new TagLimitationType(
            $this->persistenceHandlerMock,
            $this->tagsHandlerMock
        );
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue()
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
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\User\Limitation\TagLimitation $limitation
     * @param \Netgen\TagsBundle\Core\Limitation\TagLimitationType $limitationType
     */
    public function testAcceptValue(TagLimitation $limitation, TagLimitationType $limitationType)
    {
        $limitationType->acceptValue($limitation);
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValueException()
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param \Netgen\TagsBundle\Core\Limitation\TagLimitationType $limitationType
     */
    public function testAcceptValueException(Limitation $limitation, TagLimitationType $limitationType)
    {
        $limitationType->acceptValue($limitation);
    }

    /**
     * @return array
     */
    public function providerForTestValidate()
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
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\User\Limitation\TagLimitation $limitation
     * @param int $errorCount
     * @param \Netgen\TagsBundle\Core\Limitation\TagLimitationType $limitationType
     */
    public function testValidate(TagLimitation $limitation, $errorCount, TagLimitationType $limitationType)
    {
        if (!empty($limitation->limitationValues)) {
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
     *
     * @param \Netgen\TagsBundle\Core\Limitation\TagLimitationType $limitationType
     */
    public function testBuildValue(TagLimitationType $limitationType)
    {
        $expected = ['1', 2, '3'];
        $value = $limitationType->buildValue($expected);

        self::assertInstanceOf(TagLimitation::class, $value);
        self::assertInternalType('array', $value->limitationValues);
        self::assertSame($expected, $value->limitationValues);
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate()
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
     * @param mixed $expected
     */
    public function testEvaluate(TagLimitation $limitation, ValueObject $object, $expected, TagLimitationType $limitationType)
    {
        $this->userMock->expects(self::never())->method(self::anything());

        $value = $limitationType->evaluate($limitation, $this->userMock, $object);

        self::assertInternalType('boolean', $value);
        self::assertSame($expected, $value);
    }

    /**
     * @return array
     */
    public function providerForTestEvaluateInvalidArgument()
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testEvaluateInvalidArgument(Limitation $limitation, ValueObject $object, TagLimitationType $limitationType)
    {
        $this->userMock->expects(self::never())->method(self::anything());

        $limitationType->evaluate($limitation, $this->userMock, $object);
    }

    /**
     * @depends testConstruct
     * @expectedException \RuntimeException
     *
     * @param \Netgen\TagsBundle\Core\Limitation\TagLimitationType $limitationType
     */
    public function testGetCriterionInvalidValue(TagLimitationType $limitationType)
    {
        $limitationType->getCriterion(
            new TagLimitation([]),
            $this->userMock
        );
    }

    /**
     * @depends testConstruct
     *
     * @param \Netgen\TagsBundle\Core\Limitation\TagLimitationType $limitationType
     */
    public function testGetCriterionSingleValue(TagLimitationType $limitationType)
    {
        /** @var \Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId $criterion */
        $criterion = $limitationType->getCriterion(
            new TagLimitation(['limitationValues' => [1]]),
            $this->userMock
        );

        self::assertInstanceOf(TagId::class, $criterion);
        self::assertInternalType('array', $criterion->value);
        self::assertInternalType('string', $criterion->operator);
        self::assertSame(Operator::EQ, $criterion->operator);
        self::assertSame([1], $criterion->value);
    }

    /**
     * @depends testConstruct
     *
     * @param \Netgen\TagsBundle\Core\Limitation\TagLimitationType $limitationType
     */
    public function testGetCriterionMultipleValues(TagLimitationType $limitationType)
    {
        /** @var \Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId $criterion */
        $criterion = $limitationType->getCriterion(
            new TagLimitation(['limitationValues' => [1, 2]]),
            $this->userMock
        );

        self::assertInstanceOf(TagId::class, $criterion);
        self::assertInternalType('array', $criterion->value);
        self::assertInternalType('string', $criterion->operator);
        self::assertSame(Operator::IN, $criterion->operator);
        self::assertSame([1, 2], $criterion->value);
    }

    /**
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \Netgen\TagsBundle\Core\Limitation\TagLimitationType $limitationType
     */
    public function testValueSchema(TagLimitationType $limitationType)
    {
        $limitationType->valueSchema();
    }
}
