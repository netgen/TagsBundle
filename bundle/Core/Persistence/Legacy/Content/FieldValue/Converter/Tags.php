<?php

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use Netgen\TagsBundle\Core\FieldType\Tags\Type;

class Tags implements Converter
{
    const TAGS_VALIDATOR_IDENTIFIER = 'TagsValueValidator';

    public static function create()
    {
        return new self();
    }

    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
    }

    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
    }

    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        $storageDef->dataInt1 = isset($fieldDef->fieldTypeConstraints->validators[static::TAGS_VALIDATOR_IDENTIFIER]['subTreeLimit']) ?
            (int) $fieldDef->fieldTypeConstraints->validators[static::TAGS_VALIDATOR_IDENTIFIER]['subTreeLimit'] :
            0;

        $storageDef->dataInt3 = isset($fieldDef->fieldTypeConstraints->fieldSettings['hideRootTag']) ?
            (int) $fieldDef->fieldTypeConstraints->fieldSettings['hideRootTag'] :
            0;

        $storageDef->dataInt4 = isset($fieldDef->fieldTypeConstraints->validators[static::TAGS_VALIDATOR_IDENTIFIER]['maxTags']) ?
            (int) $fieldDef->fieldTypeConstraints->validators[static::TAGS_VALIDATOR_IDENTIFIER]['maxTags'] :
            0;

        $storageDef->dataText1 = isset($fieldDef->fieldTypeConstraints->fieldSettings['editView']) ?
            $fieldDef->fieldTypeConstraints->fieldSettings['editView'] :
            Type::EDIT_VIEW_DEFAULT_VALUE;
    }

    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'hideRootTag' => (bool) $storageDef->dataInt3,
                'editView' => $storageDef->dataText1,
            ],
            FieldSettings::ARRAY_AS_PROPS
        );

        $fieldDef->fieldTypeConstraints->validators = [
            static::TAGS_VALIDATOR_IDENTIFIER => [
                'subTreeLimit' => (int) $storageDef->dataInt1,
                'maxTags' => (int) $storageDef->dataInt4,
            ],
        ];
    }

    public function getIndexColumn()
    {
        return false;
    }
}
