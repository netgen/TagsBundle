<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\API\Repository\FieldType;

use DateTimeImmutable;
use eZ\Publish\API\Repository\Tests\FieldType\BaseIntegrationTest;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\FieldType\Tags\Type;
use Netgen\TagsBundle\Core\FieldType\Tags\Value as TagsValue;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\TraversableContains;
use stdClass;

class TagsIntegrationTest extends BaseIntegrationTest
{
    public function getTypeName(): string
    {
        return 'eztags';
    }

    public function getSettingsSchema(): array
    {
        return [
            'hideRootTag' => [
                'type' => 'boolean',
                'default' => false,
            ],
            'editView' => [
                'type' => 'string',
                'default' => Type::EDIT_VIEW_DEFAULT_VALUE,
            ],
        ];
    }

    public function getValidatorSchema(): array
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

    public function getValidFieldSettings(): array
    {
        return [
            'hideRootTag' => true,
            'editView' => Type::EDIT_VIEW_DEFAULT_VALUE,
        ];
    }

    public function getInvalidFieldSettings(): array
    {
        return [
            'unknown' => 42,
        ];
    }

    public function getValidValidatorConfiguration(): array
    {
        return [
            'TagsValueValidator' => [
                'subTreeLimit' => 0,
                'maxTags' => 10,
            ],
        ];
    }

    public function getInvalidValidatorConfiguration(): array
    {
        return [
            'unknown' => ['value' => 42],
        ];
    }

    public function getValidCreationFieldData(): TagsValue
    {
        return new TagsValue(
            [
                $this->getTag1(),
            ]
        );
    }

    public function assertFieldDataLoadedCorrect(Field $field): void
    {
        self::assertInstanceOf(
            TagsValue::class,
            $field->value
        );

        self::assertCount(1, $field->value->tags);
        self::assertContainsEquals($this->getTag1(), $field->value->tags);
    }

    public function provideInvalidCreationFieldData(): array
    {
        return [
            [
                42,
                InvalidArgumentType::class,
            ],
            [
                'invalid',
                InvalidArgumentType::class,
            ],
            [
                [
                    new stdClass(),
                ],
                InvalidArgumentType::class,
            ],
            [
                new stdClass(),
                InvalidArgumentType::class,
            ],
        ];
    }

    public function getValidUpdateFieldData(): TagsValue
    {
        return new TagsValue(
            [
                $this->getTag2(),
                $this->getTag3(),
            ]
        );
    }

    public function assertUpdatedFieldDataLoadedCorrect(Field $field): void
    {
        self::assertInstanceOf(
            TagsValue::class,
            $field->value
        );

        self::assertCount(2, $field->value->tags);
        self::assertContainsEquals($this->getTag2(), $field->value->tags);
        self::assertContainsEquals($this->getTag3(), $field->value->tags);
    }

    public function provideInvalidUpdateFieldData(): array
    {
        return $this->provideInvalidCreationFieldData();
    }

    public function assertCopiedFieldDataLoadedCorrectly(Field $field): void
    {
        self::assertInstanceOf(
            TagsValue::class,
            $field->value
        );

        self::assertCount(1, $field->value->tags);
        self::assertContainsEquals($this->getTag1(), $field->value->tags);
    }

    public function provideToHashData(): array
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
                        $this->getTag1(),
                    ]
                ),
                [
                    $this->getTagHash1(),
                ],
            ],
        ];
    }

    public function provideFromHashData(): array
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
                    $this->getTagHash1(),
                ],
                new TagsValue(
                    [
                        $this->getTag1(),
                    ]
                ),
            ],
        ];
    }

    public function providerForTestIsEmptyValue(): array
    {
        return [
            [new TagsValue()],
            [new TagsValue([])],
        ];
    }

    public function providerForTestIsNotEmptyValue(): array
    {
        return [
            [
                $this->getValidCreationFieldData(),
            ],
        ];
    }

    public function getFieldName(): string
    {
        return 'eztags';
    }

    /**
     * @param mixed $needle
     * @param iterable $haystack
     * @param string $message
     */
    public static function assertContainsEquals($needle, iterable $haystack, string $message = ''): void
    {
        if (method_exists(Assert::class, 'assertContainsEquals')) {
            Assert::assertContainsEquals($needle, $haystack, $message);

            return;
        }

        $constraint = new TraversableContains($needle, false, false);

        Assert::assertThat($haystack, $constraint, $message);
    }

    /**
     * Returns a tag for tests.
     */
    private function getTag1(): Tag
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
            ]
        );
    }

    /**
     * Returns a tag for tests.
     */
    private function getTag2(): Tag
    {
        return new Tag(
            [
                'id' => 8,
                'parentTagId' => 0,
                'mainTagId' => 0,
                'keywords' => ['eng-GB' => 'ez publish'],
                'depth' => 1,
                'pathString' => '/8/',
                'modificationDate' => new DateTimeImmutable('@' . 1343169159),
                'remoteId' => 'eccbc87e4b5ce2fe28308fd9f2a7baf3',
                'alwaysAvailable' => false,
                'mainLanguageCode' => 'eng-GB',
                'languageCodes' => ['eng-GB'],
            ]
        );
    }

    /**
     * Returns a tag for tests.
     */
    private function getTag3(): Tag
    {
        return new Tag(
            [
                'id' => 9,
                'parentTagId' => 47,
                'mainTagId' => 0,
                'keywords' => ['eng-GB' => 'php'],
                'depth' => 2,
                'pathString' => '/47/9/',
                'modificationDate' => new DateTimeImmutable('@' . 1343169159),
                'remoteId' => 'a87ff679a2f3e71d9181a67b7542122c',
                'alwaysAvailable' => false,
                'mainLanguageCode' => 'eng-GB',
                'languageCodes' => ['eng-GB'],
            ]
        );
    }

    /**
     * Returns a hash version of tag for tests.
     */
    private function getTagHash1(): array
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
