<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use DateTimeInterface;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class Type extends FieldType
{
    /**
     * Default edit view interface for content field.
     */
    const EDIT_VIEW_DEFAULT_VALUE = 'Default';

    /**
     * The setting keys which are available on this field type.
     *
     * The key is the setting name, and the value is the default value for given
     * setting, set to null if no particular default should be set.
     *
     * @var array
     */
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

    /**
     * The validator configuration schema.
     *
     * @var array
     */
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
    protected $tagsService;

    /**
     * @var array
     */
    protected $availableEditViews = [];

    /**
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * Sets the available edit views.
     *
     * @param array $availableEditViews
     */
    public function setEditViews(array $availableEditViews)
    {
        $this->availableEditViews = $availableEditViews;
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'eztags';
    }

    /**
     * Returns a human readable string representation from the given $value.
     *
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value $value
     *
     * @return string
     */
    public function getName(SPIValue $value)
    {
        return (string) $value;
    }

    /**
     * Returns the empty value for this field type.
     *
     * @return \Netgen\TagsBundle\Core\FieldType\Tags\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \Netgen\TagsBundle\Core\FieldType\Tags\Value
     */
    public function fromHash($hash)
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

        if (!empty($tagIds)) {
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
                        'remoteId' => isset($hashItem['remote_id']) ?
                            $hashItem['remote_id'] :
                            null,
                        'alwaysAvailable' => isset($hashItem['always_available']) ?
                            $hashItem['always_available'] :
                            true,
                    ]
                );
            } elseif (isset($loadedTags[$hashItem['id']])) {
                $tags[] = $loadedTags[$hashItem['id']];
            }
            // We ignore tags which do not exist (missing in $loadedTags)
        }

        return new Value($tags);
    }

    /**
     * Converts the given $value into a plain hash format.
     *
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value $value
     *
     * @return array
     */
    public function toHash(SPIValue $value)
    {
        $hash = [];

        foreach ($value->tags as $tag) {
            if (empty($tag->id)) {
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
                    'modified' => $tag->modificationDate instanceof DateTimeInterface ?
                        $tag->modificationDate->getTimestamp() :
                        0,
                    'remote_id' => $tag->remoteId,
                    'always_available' => $tag->alwaysAvailable,
                    'main_language_code' => $tag->mainLanguageCode,
                    'language_codes' => $tag->languageCodes,
                ];
            }
        }

        return $hash;
    }

    /**
     * Converts a $value to a persistence value.
     *
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue(SPIValue $value)
    {
        return new FieldValue(
            [
                'data' => null,
                'externalData' => $this->toHash($value),
                'sortKey' => $this->getSortInfo($value),
            ]
        );
    }

    /**
     * Converts a persistence $fieldValue to a Value.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \Netgen\TagsBundle\Core\FieldType\Tags\Value
     */
    public function fromPersistenceValue(FieldValue $fieldValue)
    {
        return $this->fromHash($fieldValue->externalData);
    }

    /**
     * Returns if the given $value is considered empty by the field type.
     *
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value $value
     *
     * @return bool
     */
    public function isEmptyValue(SPIValue $value)
    {
        return $value === null || $value->tags === $this->getEmptyValue()->tags;
    }

    /**
     * Validates the validatorConfiguration of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * @param mixed $validatorConfiguration
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateValidatorConfiguration($validatorConfiguration)
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
                        if (!is_numeric($value)) {
                            $validationErrors[] = new ValidationError(
                                "Validator parameter '%parameter%' value must be of numeric type",
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

    /**
     * Validates a field based on the validators in the field definition.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value $fieldValue The field value for which an action is performed
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $fieldValue)
    {
        $validationErrors = [];

        if ($this->isEmptyValue($fieldValue)) {
            return $validationErrors;
        }

        $validatorConfiguration = $fieldDefinition->getValidatorConfiguration();
        $constraints = isset($validatorConfiguration['TagsValueValidator']) ?
            $validatorConfiguration['TagsValueValidator'] :
            [];

        $validationErrors = [];

        if (isset($constraints['subTreeLimit']) && $constraints['subTreeLimit'] > 0) {
            foreach ($fieldValue->tags as $tag) {
                if (empty($tag->id)) {
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

        if (isset($constraints['maxTags']) && $constraints['maxTags'] > 0) {
            if (count($fieldValue->tags) > $constraints['maxTags']) {
                $validationErrors[] = new ValidationError(
                    'Number of tags must be lower or equal to %maxTags%',
                    null,
                    [
                        '%maxTags%' => $constraints['maxTags'],
                    ],
                    'value'
                );
            }
        }

        return $validationErrors;
    }

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings($fieldSettings)
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

    /**
     * Indicates if the field type supports indexing and sort keys for searching.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param mixed $inputValue
     *
     * @return \Netgen\TagsBundle\Core\FieldType\Tags\Value The potentially converted and structurally plausible value
     */
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

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value $value
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure
     */
    protected function checkValueStructure(BaseValue $value)
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
                    "{$tag}",
                    Value::class,
                    $tag
                );
            }
        }
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value $value
     *
     * @return bool
     */
    protected function getSortInfo(BaseValue $value)
    {
        return false;
    }
}
