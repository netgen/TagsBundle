<?php

namespace Netgen\TagsBundle\Tests\Core\FieldType;

use DateTime;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\FieldType\Tests\FieldTypeTest;
use eZ\Publish\Core\FieldType\ValidationError;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\FieldType\Tags\Type as TagsType;
use Netgen\TagsBundle\Core\FieldType\Tags\Value as TagsValue;
use stdClass;

/**
 * Test for eztags field type.
 *
 * @group fieldType
 * @group eztags
 */
class TagsTest extends FieldTypeTest
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tagsService;

    /**
     * Returns values for TagsService::loadTag based on input value.
     *
     * @param array $tagIds
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public function getTagsServiceLoadTagValues(array $tagIds)
    {
        $tags = [];
        foreach ($tagIds as $tagId) {
            if ($tagId < 0 || $tagId === PHP_INT_MAX) {
                continue;
            }

            $tags[$tagId] = new Tag(
                [
                    'id' => $tagId,
                ]
            );
        }

        return $tags;
    }

    /**
     * Provide data sets with field settings which are considered valid by the
     * {@see validateFieldSettings()} method.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of field settings.
     *
     * @return array
     */
    public function provideValidFieldSettings()
    {
        return [
            [
                [],
            ],
            [
                [
                    'editView' => TagsType::EDIT_VIEW_DEFAULT_VALUE,
                ],
            ],
            [
                [
                    'editView' => 'Select',
                ],
            ],
            [
                [
                    'hideRootTag' => true,
                ],
            ],
            [
                [
                    'hideRootTag' => false,
                ],
            ],
        ];
    }

    /**
     * Provide data sets with validator configurations which are considered
     * valid by the {@see validateValidatorConfiguration()} method.
     *
     * @return array
     */
    public function provideValidValidatorConfiguration()
    {
        return [
            [
                [],
            ],
            [
                [
                    'TagsValueValidator' => [],
                ],
            ],
            [
                [
                    'TagsValueValidator' => [
                        'subTreeLimit' => 0,
                    ],
                ],
            ],
            [
                [
                    'TagsValueValidator' => [
                        'subTreeLimit' => 5,
                    ],
                ],
            ],
            [
                [
                    'TagsValueValidator' => [
                        'maxTags' => 0,
                    ],
                ],
            ],
            [
                [
                    'TagsValueValidator' => [
                        'maxTags' => 10,
                    ],
                ],
            ],
        ];
    }

    /**
     * Provide data sets with field settings which are considered invalid by the
     * {@see validateFieldSettings()} method. The method must return a
     * non-empty array of validation error when receiving such field settings.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of field settings.
     *
     * @return array
     */
    public function provideInValidFieldSettings()
    {
        return [
            [
                true,
            ],
            [
                [
                    'nonExistingKey' => 42,
                ],
            ],
            [
                [
                    'editView' => 'Unknown',
                ],
            ],
            [
                [
                    'hideRootTag' => 42,
                ],
            ],
        ];
    }

    /**
     * Provide data sets with validator configurations which are considered
     * invalid by the {@see validateValidatorConfiguration()} method. The
     * method must return a non-empty array of validation errors when receiving
     * one of the provided values.
     *
     * @return array
     */
    public function provideInvalidValidatorConfiguration()
    {
        return [
            [
                true,
            ],
            [
                [
                    'NonExistentValidator' => [],
                ],
            ],
            [
                [
                    'TagsValueValidator' => true,
                ],
            ],
            [
                [
                    'TagsValueValidator' => [
                        'nonExistentParameter' => 42,
                    ],
                ],
            ],
            [
                [
                    'TagsValueValidator' => [
                        'subTreeLimit' => true,
                    ],
                ],
            ],
            [
                [
                    'TagsValueValidator' => [
                        'subTreeLimit' => -5,
                    ],
                ],
            ],
            [
                [
                    'TagsValueValidator' => [
                        'subTreeLimit' => PHP_INT_MAX,
                    ],
                ],
            ],
            [
                [
                    'TagsValueValidator' => [
                        'maxTags' => true,
                    ],
                ],
            ],
            [
                [
                    'TagsValueValidator' => [
                        'maxTags' => -5,
                    ],
                ],
            ],
        ];
    }

    /**
     * Data provider for invalid input to acceptValue().
     *
     * @return array
     */
    public function provideInvalidInputForAcceptValue()
    {
        return [
            [
                42,
                InvalidArgumentException::class,
            ],
            [
                'invalid',
                InvalidArgumentException::class,
            ],
            [
                [
                    new stdClass(),
                ],
                InvalidArgumentException::class,
            ],
            [
                new stdClass(),
                InvalidArgumentException::class,
            ],
        ];
    }

    /**
     * Data provider for valid input to acceptValue().
     *
     * @return array
     */
    public function provideValidInputForAcceptValue()
    {
        return [
            [
                null,
                new TagsValue(),
            ],
            [
                [],
                new TagsValue(),
            ],
            [
                [new Tag()],
                new TagsValue([new Tag()]),
            ],
            [
                new TagsValue(),
                new TagsValue(),
            ],
            [
                new TagsValue([]),
                new TagsValue(),
            ],
            [
                new TagsValue([new Tag()]),
                new TagsValue([new Tag()]),
            ],
        ];
    }

    /**
     * Provides data sets with validator configuration and/or field settings and
     * field value which are considered valid by the {@see validate()} method.
     *
     * @return array
     */
    public function provideValidDataForValidate()
    {
        return [
            [
                [
                    'validatorConfiguration' => [
                        'TagsValueValidator' => [
                            'subTreeLimit' => 0,
                        ],
                    ],
                ],
                new TagsValue([new Tag(['id' => 102, 'pathString' => '/2/42/102/'])]),
            ],
            [
                [
                    'validatorConfiguration' => [
                        'TagsValueValidator' => [
                            'subTreeLimit' => 42,
                        ],
                    ],
                ],
                new TagsValue([new Tag(['id' => 42, 'pathString' => '/2/42/'])]),
            ],
            [
                [
                    'validatorConfiguration' => [
                        'TagsValueValidator' => [
                            'subTreeLimit' => 42,
                        ],
                    ],
                ],
                new TagsValue([new Tag(['id' => 102, 'pathString' => '/2/42/102/'])]),
            ],
            [
                [
                    'validatorConfiguration' => [
                        'TagsValueValidator' => [
                            'maxTags' => 0,
                        ],
                    ],
                ],
                new TagsValue(),
            ],
            [
                [
                    'validatorConfiguration' => [
                        'TagsValueValidator' => [
                            'maxTags' => 0,
                        ],
                    ],
                ],
                new TagsValue([new Tag(), new Tag()]),
            ],
            [
                [
                    'validatorConfiguration' => [
                        'TagsValueValidator' => [
                            'maxTags' => 2,
                        ],
                    ],
                ],
                new TagsValue(),
            ],
            [
                [
                    'validatorConfiguration' => [
                        'TagsValueValidator' => [
                            'maxTags' => 2,
                        ],
                    ],
                ],
                new TagsValue([new Tag()]),
            ],
            [
                [
                    'validatorConfiguration' => [
                        'TagsValueValidator' => [
                            'maxTags' => 2,
                        ],
                    ],
                ],
                new TagsValue([new Tag(), new Tag()]),
            ],
        ];
    }

    /**
     * Provides data sets with validator configuration and/or field settings,
     * field value and corresponding validation errors returned by
     * the {@see validate()} method.
     *
     * @return array
     */
    public function provideInvalidDataForValidate()
    {
        return [
            [
                [
                    'validatorConfiguration' => [
                        'TagsValueValidator' => [
                            'subTreeLimit' => 42,
                        ],
                    ],
                ],
                new TagsValue(
                    [
                        new Tag(
                            [
                                'id' => 102,
                                'pathString' => '/2/43/102/',
                                'keywords' => ['eng-GB' => 'test'],
                                'mainLanguageCode' => 'eng-GB',
                            ]
                        ),
                    ]
                ),
                [
                    new ValidationError(
                        'Tag "%keyword%" is not below tag with ID %subTreeLimit% as specified by field definition',
                        null,
                        [
                            '%keyword%' => 'test',
                            '%subTreeLimit%' => 42,
                        ],
                        'value'
                    ),
                ],
            ],
            [
                [
                    'validatorConfiguration' => [
                        'TagsValueValidator' => [
                            'maxTags' => 2,
                        ],
                    ],
                ],
                new TagsValue([new Tag(), new Tag(), new Tag()]),
                [
                    new ValidationError(
                        'Number of tags must be lower or equal to %maxTags%',
                        null,
                        [
                            '%maxTags%' => 2,
                        ],
                        'value'
                    ),
                ],
            ],
        ];
    }

    /**
     * Provide input for the toHash() method.
     *
     * @return array
     */
    public function provideInputForToHash()
    {
        return [
            [
                new TagsValue(),
                [],
            ],
            [
                new TagsValue([]),
                [],
            ],
            [
                new TagsValue(
                    [
                        $this->getTag(),
                    ]
                ),
                [
                    $this->getTagHash(),
                ],
            ],
        ];
    }

    /**
     * Provide input to fromHash() method.
     *
     * @return array
     */
    public function provideInputForFromHash()
    {
        return [
            [
                null,
                new TagsValue(),
            ],
            [
                [],
                new TagsValue(),
            ],
            [
                [
                    $this->getTagHash(),
                ],
                new TagsValue(
                    [
                        new Tag(
                            [
                                'id' => 40,
                            ]
                        ),
                    ]
                ),
            ],
        ];
    }

    /**
     * Provides data for the getName() test.
     *
     * @return array
     */
    public function provideDataForGetName()
    {
        return [
            [
                new TagsValue(),
                '',
            ],
            [
                new TagsValue([]),
                '',
            ],
            [
                new TagsValue(
                    [
                        $this->getTag(),
                    ]
                ),
                'eztags',
            ],
            [
                new TagsValue(
                    [
                        $this->getTag(),
                        $this->getTag(),
                    ]
                ),
                'eztags, eztags',
            ],
        ];
    }

    /**
     * Returns the identifier of the field type under test.
     *
     * @return string
     */
    protected function provideFieldTypeIdentifier()
    {
        return 'eztags';
    }

    /**
     * Returns the field type under test.
     *
     * @return \Netgen\TagsBundle\Core\FieldType\Tags\Type
     */
    protected function createFieldTypeUnderTest()
    {
        $this->tagsService = $this->createMock(TagsService::class);

        $this->tagsService->expects(self::any())
            ->method('loadTagList')
            ->will(self::returnCallback([$this, 'getTagsServiceLoadTagValues']));

        $tagsType = new TagsType($this->tagsService);
        $tagsType->setEditViews(
            [
                'default' => ['identifier' => 'Default'],
                'select' => ['identifier' => 'Select'],
            ]
        );

        return $tagsType;
    }

    /**
     * Returns the settings schema expected from the field type.
     *
     * @return array
     */
    protected function getSettingsSchemaExpectation()
    {
        return [
            'hideRootTag' => [
                'type' => 'boolean',
                'default' => false,
            ],
            'editView' => [
                'type' => 'string',
                'default' => TagsType::EDIT_VIEW_DEFAULT_VALUE,
            ],
        ];
    }

    /**
     * Returns the validator configuration schema expected from the field type.
     *
     * @return array
     */
    protected function getValidatorConfigurationSchemaExpectation()
    {
        return [
            'TagsValueValidator' => [
                'subTreeLimit' => [
                    'type' => 'int',
                    'default' => 0,
                ],
                'maxTags' => [
                    'type' => 'int',
                    'default' => 0,
                ],
            ],
        ];
    }

    /**
     * Returns the empty value expected from the field type.
     *
     * @return \Netgen\TagsBundle\Core\FieldType\Tags\Value
     */
    protected function getEmptyValueExpectation()
    {
        return new TagsValue();
    }

    /**
     * Returns a tag for tests.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected function getTag()
    {
        $modificationDate = new DateTime();
        $modificationDate->setTimestamp(1308153110);

        return new Tag(
            [
                'id' => 40,
                'parentTagId' => 7,
                'mainTagId' => 0,
                'keywords' => ['eng-GB' => 'eztags'],
                'depth' => 3,
                'pathString' => '/8/7/40/',
                'modificationDate' => $modificationDate,
                'remoteId' => '182be0c5cdcd5072bb1864cdee4d3d6e',
                'alwaysAvailable' => false,
                'mainLanguageCode' => 'eng-GB',
                'languageCodes' => ['eng-GB'],
            ]
        );
    }

    /**
     * Returns a hash version of tag for tests.
     *
     * @return array
     */
    protected function getTagHash()
    {
        return [
            'id' => 40,
            'parent_id' => 7,
            'main_tag_id' => 0,
            'keywords' => ['eng-GB' => 'eztags'],
            'depth' => 3,
            'path_string' => '/8/7/40/',
            'modified' => 1308153110,
            'remote_id' => '182be0c5cdcd5072bb1864cdee4d3d6e',
            'always_available' => false,
            'main_language_code' => 'eng-GB',
            'language_codes' => ['eng-GB'],
        ];
    }
}
