<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class Type extends FieldType
{
    /**
     * Default edit view interface for content field.
     */
    public const EDIT_VIEW_DEFAULT_VALUE = 'Default';

    protected $settingsSchema = [
        'hideRootTag' => [
            'type' => 'boolean',
            'default' => false,
        ],
        'editView' => [
            'type' => 'string',
            'default' => self::EDIT_VIEW_DEFAULT_VALUE,
        ],
    ];

    protected $validatorConfigurationSchema = [
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

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var array
     */
    private $availableEditViews = [];

    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * Sets the available edit views.
     */
    public function setEditViews(array $availableEditViews): void
    {
        $this->availableEditViews = $availableEditViews;
    }

    public function getFieldTypeIdentifier(): string
    {
        return 'eztags';
    }

    public function getName(SPIValue $value, FieldDefinition $fieldDefinition, string $languageCode): string
    {
        return (string) $value;
    }

    public function getEmptyValue(): Value
    {
        return new Value();
    }

    public function fromHash($hash): Value
    {
        if (!is_array($hash)) {
            return new Value();
        }

        $tags = [];
        $tagIds = [];
        foreach ($hash as $hashItem) {
            if (isset($hashItem['id'])) {
                $tagIds[] = $hashItem['id'];
            }
        }

        if (count($tagIds) > 0) {
            $loadedTags = $this->tagsService->loadTagList($tagIds);
        } else {
            $loadedTags = [];
        }

        foreach ($hash as $hashItem) {
            if (!is_array($hashItem)) {
                continue;
            }

            if (!isset($hashItem['id'])) {
                $tags[] = new Tag(
                    [
                        'parentTagId' => $hashItem['parent_id'],
                        'keywords' => $hashItem['keywords'],
                        'mainLanguageCode' => $hashItem['main_language_code'],
                        'remoteId' => $hashItem['remote_id'] ?? null,
                        'alwaysAvailable' => $hashItem['always_available'] ?? true,
                    ]
                );
            } elseif (isset($loadedTags[$hashItem['id']])) {
                $tags[] = $loadedTags[$hashItem['id']];
            }
            // We ignore tags which do not exist (missing in $loadedTags)
        }

        return new Value($tags);
    }

    public function toHash(SPIValue $value): array
    {
        $hash = [];

        foreach ($value->tags as $tag) {
            if ($tag->id === null || $tag->id < 1) {
                $hash[] = [
                    'parent_id' => $tag->parentTagId,
                    'keywords' => $tag->keywords,
                    'remote_id' => $tag->remoteId,
                    'always_available' => $tag->alwaysAvailable,
                    'main_language_code' => $tag->mainLanguageCode,
                ];
            } else {
                $hash[] = [
                    'id' => $tag->id,
                    'parent_id' => $tag->parentTagId,
                    'main_tag_id' => $tag->mainTagId,
                    'keywords' => $tag->keywords,
                    'depth' => $tag->depth,
                    'path_string' => $tag->pathString,
                    'modified' => $tag->modificationDate->getTimestamp(),
                    'remote_id' => $tag->remoteId,
                    'always_available' => $tag->alwaysAvailable,
                    'main_language_code' => $tag->mainLanguageCode,
                    'language_codes' => $tag->languageCodes,
                ];
            }
        }

        return $hash;
    }

    public function toPersistenceValue(SPIValue $value): FieldValue
    {
        return new FieldValue(
            [
                'data' => null,
                'externalData' => $this->toHash($value),
                'sortKey' => $this->getSortInfo($value),
            ]
        );
    }

    public function fromPersistenceValue(FieldValue $fieldValue): Value
    {
        return $this->fromHash($fieldValue->externalData);
    }

    public function isEmptyValue(SPIValue $value): bool
    {
        return $value === null || $value->tags === $this->getEmptyValue()->tags;
    }

    public function validateValidatorConfiguration($validatorConfiguration): array
    {
        $validationErrors = [];

        if (!is_array($validatorConfiguration)) {
            $validationErrors[] = new ValidationError('Validator configuration must be in form of an array');

            return $validationErrors;
        }

        foreach ($validatorConfiguration as $validatorIdentifier => $constraints) {
            if ($validatorIdentifier !== 'TagsValueValidator') {
                $validationErrors[] = new ValidationError(
                    "Validator '%validator%' is unknown",
                    null,
                    [
                        '%validator%' => $validatorIdentifier,
                    ],
                    "[{$validatorIdentifier}]"
                );

                continue;
            }

            if (!is_array($constraints)) {
                $validationErrors[] = new ValidationError('TagsValueValidator constraints must be in form of an array');

                return $validationErrors;
            }

            foreach ($constraints as $name => $value) {
                switch ($name) {
                    case 'subTreeLimit':
                        if (!is_int($value)) {
                            $validationErrors[] = new ValidationError(
                                "Validator parameter '%parameter%' value must be of int type",
                                null,
                                [
                                    '%parameter%' => $name,
                                ],
                                "[{$validatorIdentifier}][{$name}]"
                            );
                        }

                        if ($value < 0) {
                            $validationErrors[] = new ValidationError(
                                "Validator parameter '%parameter%' value must be equal or larger than 0",
                                null,
                                [
                                    '%parameter%' => $name,
                                ],
                                "[{$validatorIdentifier}][{$name}]"
                            );
                        }

                        if ($value > 0 && !$this->tagsService->loadTagList([$value])) {
                            $validationErrors[] = new ValidationError(
                                "Validator parameter '%parameter%' value must be an existing tag ID",
                                null,
                                [
                                    '%parameter%' => $name,
                                ],
                                "[{$validatorIdentifier}][{$name}]"
                            );
                        }

                        break;
                    case 'maxTags':
                        if (!is_int($value)) {
                            $validationErrors[] = new ValidationError(
                                "Validator parameter '%parameter%' value must be of integer type",
                                null,
                                [
                                    '%parameter%' => $name,
                                ],
                                "[{$validatorIdentifier}][{$name}]"
                            );
                        }

                        if ($value < 0) {
                            $validationErrors[] = new ValidationError(
                                "Validator parameter '%parameter%' value must be equal or larger than 0",
                                null,
                                [
                                    '%parameter%' => $name,
                                ],
                                "[{$validatorIdentifier}][{$name}]"
                            );
                        }

                        break;
                    default:
                        $validationErrors[] = new ValidationError(
                            "Validator parameter '%parameter%' is unknown",
                            null,
                            [
                                '%parameter%' => $name,
                            ],
                            "[{$validatorIdentifier}][{$name}]"
                        );
                }
            }
        }

        return $validationErrors;
    }

    public function validate(FieldDefinition $fieldDefinition, SPIValue $fieldValue): array
    {
        $validationErrors = [];

        if ($this->isEmptyValue($fieldValue)) {
            return $validationErrors;
        }

        $validatorConfiguration = $fieldDefinition->getValidatorConfiguration();
        $constraints = $validatorConfiguration['TagsValueValidator'] ?? [];

        $validationErrors = [];

        if (($constraints['subTreeLimit'] ?? 0) > 0) {
            foreach ($fieldValue->tags as $tag) {
                if ($tag->id === null || $tag->id < 1) {
                    $tag = $this->tagsService->loadTag($tag->parentTagId);
                }

                if (!in_array($constraints['subTreeLimit'], $tag->path, true)) {
                    $validationErrors[] = new ValidationError(
                        'Tag "%keyword%" is not below tag with ID %subTreeLimit% as specified by field definition',
                        null,
                        [
                            '%keyword%' => $tag->getKeyword(),
                            '%subTreeLimit%' => $constraints['subTreeLimit'],
                        ],
                        'value'
                    );

                    break;
                }
            }
        }

        if (($constraints['maxTags'] ?? 0) > 0 && count($fieldValue->tags) > $constraints['maxTags']) {
            $validationErrors[] = new ValidationError(
                'Number of tags must be lower or equal to %maxTags%',
                null,
                [
                    '%maxTags%' => $constraints['maxTags'],
                ],
                'value'
            );
        }

        return $validationErrors;
    }

    public function validateFieldSettings($fieldSettings): array
    {
        $validationErrors = [];

        if (!is_array($fieldSettings)) {
            $validationErrors[] = new ValidationError('Field settings must be in form of an array');

            return $validationErrors;
        }

        foreach ($fieldSettings as $name => $value) {
            if (!isset($this->settingsSchema[$name])) {
                $validationErrors[] = new ValidationError(
                    'Setting "%setting%" is unknown',
                    null,
                    [
                        '%setting%' => $name,
                    ],
                    "[{$name}]"
                );

                continue;
            }

            switch ($name) {
                case 'hideRootTag':
                    if (!is_bool($value)) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of boolean type",
                            null,
                            [
                                '%setting%' => $name,
                            ],
                            "[{$name}]"
                        );
                    }

                    break;
                case 'editView':
                    if (!is_string($value)) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of string type",
                            null,
                            [
                                '%setting%' => $name,
                            ],
                            "[{$name}]"
                        );
                    }

                    $editViewExists = false;
                    foreach ($this->availableEditViews as $editView) {
                        if ($editView['identifier'] === $value) {
                            $editViewExists = true;

                            break;
                        }
                    }

                    if (!$editViewExists) {
                        $validationErrors[] = new ValidationError(
                            "Edit view '%editView%' does not exist",
                            null,
                            [
                                '%editView%' => $value,
                            ],
                            "[{$name}]"
                        );
                    }

                    break;
            }
        }

        return $validationErrors;
    }

    public function isSearchable(): bool
    {
        return true;
    }

    protected function createValueFromInput($inputValue)
    {
        if (is_array($inputValue)) {
            foreach ($inputValue as $inputValueItem) {
                if (!$inputValueItem instanceof Tag) {
                    return $inputValue;
                }
            }

            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    protected function checkValueStructure(BaseValue $value): void
    {
        if (!is_array($value->tags)) {
            throw new InvalidArgumentType(
                '$value->tags',
                'array',
                $value->tags
            );
        }

        foreach ($value->tags as $tag) {
            if (!$tag instanceof Tag) {
                throw new InvalidArgumentType(
                    var_export($tag, true),
                    Value::class,
                    $tag
                );
            }
        }
    }

    protected function getSortInfo(BaseValue $value): bool
    {
        return false;
    }
}
