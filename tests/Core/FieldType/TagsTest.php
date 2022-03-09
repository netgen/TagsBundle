<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Core\FieldType;

use DateTimeImmutable;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\FieldType\ValidationError;
use Ibexa\Tests\Core\FieldType\FieldTypeTest;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagList;
use Netgen\TagsBundle\Core\FieldType\Tags\Type;
use Netgen\TagsBundle\Core\FieldType\Tags\Type as TagsType;
use Netgen\TagsBundle\Core\FieldType\Tags\Value;
use Netgen\TagsBundle\Core\FieldType\Tags\Value as TagsValue;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use const PHP_INT_MAX;

final class TagsTest extends FieldTypeTest
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService&\PHPUnit\Framework\MockObject\MockObject
     */
    private MockObject $tagsService;

    /**
     * Returns values for TagsService::loadTag based on input value.
     */
    public function getTagsServiceLoadTagValues(array $tagIds): TagList
    {
        $tags = [];
        foreach ($tagIds as $tagId) {
            if ($tagId < 0 || $tagId === PHP_INT_MAX) {
                continue;
            }

            $tags[$tagId] = new Tag(
                [
                    'id' => $tagId,
                    'parentTagId' => 0,
                    'mainTagId' => 0,
                    'keywords' => [],
                    'depth' => 1,
                    'pathString' => '/' . $tagId . '/',
                    'path' => [$tagId],
                    'modificationDate' => new DateTimeImmutable('@' . 1308153110),
                    'remoteId' => '',
                    'alwaysAvailable' => true,
                    'mainLanguageCode' => 'eng-GB',
                    'languageCodes' => ['eng-GB'],
                    'prioritizedLanguageCode' => 'eng-GB',
                ]
            );
        }

        return new TagList($tags);
    }

    public function provideValidFieldSettings(): array
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

    public function provideValidValidatorConfiguration(): array
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

    public function provideInValidFieldSettings(): array
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

    public function provideInvalidValidatorConfiguration(): array
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

    public function provideInvalidInputForAcceptValue(): array
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

    public function provideValidInputForAcceptValue(): array
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

    public function provideValidDataForValidate(): array
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

    public function provideInvalidDataForValidate(): array
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

    public function provideInputForToHash(): array
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

    public function provideInputForFromHash(): array
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
                                'parentTagId' => 0,
                                'mainTagId' => 0,
                                'keywords' => [],
                                'depth' => 1,
                                'pathString' => '/40/',
                                'path' => [40],
                                'modificationDate' => new DateTimeImmutable('@' . 1308153110),
                                'remoteId' => '',
                                'alwaysAvailable' => true,
                                'mainLanguageCode' => 'eng-GB',
                                'languageCodes' => ['eng-GB'],
                                'prioritizedLanguageCode' => 'eng-GB',
                            ]
                        ),
                    ]
                ),
            ],
        ];
    }

    public function provideDataForGetName(): array
    {
        return [
            [
                new TagsValue(),
                '',
                [],
                'eng-GB',
            ],
            [
                new TagsValue([]),
                '',
                [],
                'eng-GB',
            ],
            [
                new TagsValue(
                    [
                        $this->getTag(),
                    ]
                ),
                'eztags',
                [],
                'eng-GB',
            ],
            [
                new TagsValue(
                    [
                        $this->getTag(),
                        $this->getTag(),
                    ]
                ),
                'eztags, eztags',
                [],
                'eng-GB',
            ],
        ];
    }

    protected function provideFieldTypeIdentifier(): string
    {
        return 'eztags';
    }

    protected function createFieldTypeUnderTest(): Type
    {
        $this->tagsService = $this->createMock(TagsService::class);

        $this->tagsService->expects(self::any())
            ->method('loadTagList')
            ->willReturnCallback([$this, 'getTagsServiceLoadTagValues']);

        $configResolverMock = $this->createMock(ConfigResolverInterface::class);
        $configResolverMock
            ->expects(self::any())
            ->method('getParameter')
            ->with(
                self::identicalTo('edit_views'),
                self::identicalTo('netgen_tags')
            )
            ->willReturn(
                [
                    'default' => ['identifier' => 'Default'],
                    'select' => ['identifier' => 'Select'],
                ]
            );

        return new TagsType($this->tagsService, $configResolverMock);
    }

    protected function getSettingsSchemaExpectation(): array
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

    protected function getValidatorConfigurationSchemaExpectation(): array
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

    protected function getEmptyValueExpectation(): Value
    {
        return new TagsValue();
    }

    /**
     * Returns a tag for tests.
     */
    private function getTag(): Tag
    {
        return new Tag(
            [
                'id' => 40,
                'parentTagId' => 7,
                'mainTagId' => 0,
                'keywords' => ['eng-GB' => 'eztags'],
                'depth' => 3,
                'pathString' => '/8/7/40/',
                'modificationDate' => new DateTimeImmutable('@' . 1308153110),
                'remoteId' => '182be0c5cdcd5072bb1864cdee4d3d6e',
                'alwaysAvailable' => false,
                'mainLanguageCode' => 'eng-GB',
                'languageCodes' => ['eng-GB'],
                'prioritizedLanguageCode' => 'eng-GB',
            ]
        );
    }

    /**
     * Returns a hash version of tag for tests.
     */
    private function getTagHash(): array
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
