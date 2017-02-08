<?php

namespace Netgen\TagsBundle\Tests\Core\Limitation;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Limitation\Tests\Base;
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
     * Setup Tags Handler mock.
     */
    public function setUp()
    {
        parent::setUp();

        $this->tagsHandlerMock = $this->getMock(
            Handler::class,
            array(),
            array(),
            '',
            false
        );
    }

    /**
     * Tear down Location Handler mock.
     */
    public function tearDown()
    {
        unset($this->tagsHandlerMock);
        parent::tearDown();
    }

    /**
     * @return \Netgen\TagsBundle\Core\Limitation\TagLimitationType
     */
    public function testConstruct()
    {
        return new TagLimitationType(
            $this->getPersistenceMock(),
            $this->tagsHandlerMock
        );
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue()
    {
        return array(
            array(new TagLimitation()),
            array(new TagLimitation(array())),
            array(
                new TagLimitation(
                    array(
                        'limitationValues' => array(
                            '1',
                            '2',
                            '3',
                        ),
                    )
                ),
            ),
            array(
                new TagLimitation(
                    array(
                        'limitationValues' => array(
                            1,
                            2,
                            3,
                        ),
                    )
                ),
            ),
            array(
                new TagLimitation(
                    array(
                        'limitationValues' => array(
                            1,
                            '2',
                            '3',
                        ),
                    )
                ),
            ),
        );
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
        return array(
            array(new ObjectStateLimitation()),
            array(
                new TagLimitation(
                    array(
                        'limitationValues' => true,
                    )
                ),
            ),
            array(
                new TagLimitation(
                    array(
                        'limitationValues' => array(true),
                    )
                ),
            ),
        );
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
        return array(
            array(
                new TagLimitation(),
                0,
            ),
            array(
                new TagLimitation(
                    array()
                ),
                0,
            ),
            array(
                new TagLimitation(
                    array(
                        'limitationValues' => array(1, 2, 3),
                    )
                ),
                0,
            ),
            array(
                new TagLimitation(
                    array(
                        'limitationValues' => array('1', '2', '3'),
                    )
                ),
                0,
            ),
            array(
                new TagLimitation(
                    array(
                        'limitationValues' => array('1', 2, 3),
                    )
                ),
                0,
            ),
            array(
                new TagLimitation(
                    array(
                        'limitationValues' => array(true),
                    )
                ),
                1,
            ),
            array(
                new TagLimitation(
                    array(
                        'limitationValues' => array('1', false),
                    )
                ),
                1,
            ),
            array(
                new TagLimitation(
                    array(
                        'limitationValues' => array('1', 2, false),
                    )
                ),
                1,
            ),
            array(
                new TagLimitation(
                    array(
                        'limitationValues' => array(null, array()),
                    )
                ),
                2,
            ),
        );
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
                        ->expects($this->at($key))
                        ->method('loadTagInfo')
                        ->with($value)
                        ->will(
                            $this->returnValue(
                                new TagInfo(array('id' => $value))
                            )
                        );
                } else {
                    $this->tagsHandlerMock
                        ->expects($this->at($key))
                        ->method('loadTagInfo')
                        ->with($value)
                        ->will($this->throwException(new NotFoundException('tag', $value)));
                }
            }
        } else {
            $this->tagsHandlerMock
                ->expects($this->never())
                ->method($this->anything());
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
        $expected = array('1', 2, '3');
        $value = $limitationType->buildValue($expected);

        self::assertInstanceOf(TagLimitation::class, $value);
        self::assertInternalType('array', $value->limitationValues);
        self::assertEquals($expected, $value->limitationValues);
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate()
    {
        return array(
            // Tag with no access
            array(
                'limitation' => new TagLimitation(),
                'object' => new Tag(array('id' => 1)),
                'expected' => false,
            ),
            // Tag with no access
            array(
                'limitation' => new TagLimitation(array('limitationValues' => array(2))),
                'object' => new Tag(array('id' => 1)),
                'expected' => false,
            ),
            // Tag with access
            array(
                'limitation' => new TagLimitation(array('limitationValues' => array(1))),
                'object' => new Tag(array('id' => 1)),
                'expected' => true,
            ),
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestEvaluate
     * @param mixed $expected
     */
    public function testEvaluate(TagLimitation $limitation, ValueObject $object, $expected, TagLimitationType $limitationType)
    {
        $userMock = $this->getUserMock();
        $userMock->expects($this->never())->method($this->anything());

        $value = $limitationType->evaluate($limitation, $userMock, $object);

        self::assertInternalType('boolean', $value);
        self::assertEquals($expected, $value);
    }

    /**
     * @return array
     */
    public function providerForTestEvaluateInvalidArgument()
    {
        return array(
            // invalid limitation
            array(
                'limitation' => new ObjectStateLimitation(),
                'object' => new Tag(),
            ),
            // invalid object
            array(
                'limitation' => new TagLimitation(),
                'object' => new ObjectStateLimitation(),
            ),
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestEvaluateInvalidArgument
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testEvaluateInvalidArgument(Limitation $limitation, ValueObject $object, TagLimitationType $limitationType)
    {
        $userMock = $this->getUserMock();
        $userMock->expects($this->never())->method($this->anything());

        $limitationType->evaluate($limitation, $userMock, $object);
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
            new TagLimitation(array()),
            $this->getUserMock()
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
            new TagLimitation(array('limitationValues' => array(1))),
            $this->getUserMock()
        );

        self::assertInstanceOf(TagId::class, $criterion);
        self::assertInternalType('array', $criterion->value);
        self::assertInternalType('string', $criterion->operator);
        self::assertEquals(Operator::EQ, $criterion->operator);
        self::assertEquals(array(1), $criterion->value);
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
            new TagLimitation(array('limitationValues' => array(1, 2))),
            $this->getUserMock()
        );

        self::assertInstanceOf(TagId::class, $criterion);
        self::assertInternalType('array', $criterion->value);
        self::assertInternalType('string', $criterion->operator);
        self::assertEquals(Operator::IN, $criterion->operator);
        self::assertEquals(array(1, 2), $criterion->value);
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
