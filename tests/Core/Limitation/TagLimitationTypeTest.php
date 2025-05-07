<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Core\Limitation;

use Ibexa\Contracts\Core\Persistence\Handler as PersistenceHandler;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ObjectStateLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Tests\Core\Limitation\Base;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\User\Limitation\TagLimitation;
use Netgen\TagsBundle\Core\Limitation\TagLimitationType;
use Netgen\TagsBundle\SPI\Persistence\Tags\Handler;
use Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

use function count;
use function is_int;

final class TagLimitationTypeTest extends Base
{
    private Handler&MockObject $tagsHandlerMock;

    private MockObject&PersistenceHandler $persistenceHandlerMock;

    private MockObject&User $userMock;

    private TagLimitationType $limitationType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->persistenceHandlerMock = $this->createMock(PersistenceHandler::class);
        $this->userMock = $this->createMock(User::class);
        $this->tagsHandlerMock = $this->createMock(Handler::class);

        $this->limitationType = new TagLimitationType(
            $this->persistenceHandlerMock,
            $this->tagsHandlerMock,
        );
    }

    /**
     * @dataProvider provideAcceptValueCases
     */
    public function testAcceptValue(TagLimitation $limitation): void
    {
        $this->limitationType->acceptValue($limitation);

        // Fake assertion count to remove the risky flag
        $this->addToAssertionCount(1);
    }

    public static function provideAcceptValueCases(): iterable
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
                    ],
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
                    ],
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
                    ],
                ),
            ],
        ];
    }

    /**
     * @dataProvider provideAcceptValueExceptionCases
     */
    public function testAcceptValueException(Limitation $limitation): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->limitationType->acceptValue($limitation);
    }

    public static function provideAcceptValueExceptionCases(): iterable
    {
        return [
            [new ObjectStateLimitation()],
            [
                new TagLimitation(
                    [
                        'limitationValues' => true,
                    ],
                ),
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => [true],
                    ],
                ),
            ],
        ];
    }

    /**
     * @dataProvider provideValidateCases
     */
    public function testValidate(TagLimitation $limitation, int $errorCount): void
    {
        if ($limitation->limitationValues !== null && count($limitation->limitationValues) > 0) {
            foreach ($limitation->limitationValues as $key => $value) {
                if (is_int($value)) {
                    $this->tagsHandlerMock
                        ->expects(self::at($key))
                        ->method('loadTagInfo')
                        ->with($value)
                        ->willReturn(
                            new TagInfo(['id' => $value]),
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

        $validationErrors = $this->limitationType->validate($limitation);
        self::assertCount($errorCount, $validationErrors);
    }

    public static function provideValidateCases(): iterable
    {
        return [
            [
                new TagLimitation(),
                0,
            ],
            [
                new TagLimitation(
                    [],
                ),
                0,
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => [1, 2, 3],
                    ],
                ),
                0,
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => ['1', '2', '3'],
                    ],
                ),
                3,
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => ['1', 2, 3],
                    ],
                ),
                1,
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => [true],
                    ],
                ),
                1,
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => ['1', false],
                    ],
                ),
                2,
            ],
            [
                new TagLimitation(
                    [
                        'limitationValues' => ['1', 2, false],
                    ],
                ),
                2,
            ],
        ];
    }

    public function testBuildValue(): void
    {
        $value = $this->limitationType->buildValue(['1', 2, '3']);

        self::assertInstanceOf(TagLimitation::class, $value);
        self::assertSame([1, 2, 3], $value->limitationValues);
    }

    /**
     * @dataProvider provideEvaluateCases
     */
    public function testEvaluate(TagLimitation $limitation, ValueObject $object, mixed $expected): void
    {
        $this->userMock->expects(self::never())->method(self::anything());

        $value = $this->limitationType->evaluate($limitation, $this->userMock, $object);

        self::assertSame($expected, $value);
    }

    public static function provideEvaluateCases(): iterable
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
     * @dataProvider provideEvaluateInvalidArgumentCases
     */
    public function testEvaluateInvalidArgument(Limitation $limitation, ValueObject $object): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->userMock->expects(self::never())->method(self::anything());

        $this->limitationType->evaluate($limitation, $this->userMock, $object);
    }

    public static function provideEvaluateInvalidArgumentCases(): iterable
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

    public function testGetCriterionInvalidValue(): void
    {
        $this->expectException(RuntimeException::class);

        $this->limitationType->getCriterion(
            new TagLimitation([]),
            $this->userMock,
        );
    }

    public function testGetCriterionSingleValue(): void
    {
        $criterion = $this->limitationType->getCriterion(
            new TagLimitation(['limitationValues' => [1]]),
            $this->userMock,
        );

        self::assertInstanceOf(TagId::class, $criterion);
        self::assertSame(Operator::EQ, $criterion->operator);
        self::assertSame([1], $criterion->value);
    }

    public function testGetCriterionMultipleValues(): void
    {
        $criterion = $this->limitationType->getCriterion(
            new TagLimitation(['limitationValues' => [1, 2]]),
            $this->userMock,
        );

        self::assertInstanceOf(TagId::class, $criterion);
        self::assertSame(Operator::IN, $criterion->operator);
        self::assertSame([1, 2], $criterion->value);
    }

    public function testValueSchema(): void
    {
        $this->expectException(NotImplementedException::class);

        $this->limitationType->valueSchema();
    }
}
