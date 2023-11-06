<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter;

use Ibexa\Contracts\Core\Persistence\Content\FieldValue;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Core\FieldType\FieldSettings;
use Ibexa\Core\Persistence\Legacy\Content\FieldValue\Converter;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldValue;
use Netgen\TagsBundle\Core\FieldType\Tags\Type;

final class Tags implements Converter
{
    private const TAGS_VALIDATOR_IDENTIFIER = 'TagsValueValidator';

    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue): void {}

    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue): void {}

    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef): void
    {
        $storageDef->dataInt1 = (int) ($fieldDef->fieldTypeConstraints->validators[self::TAGS_VALIDATOR_IDENTIFIER]['subTreeLimit'] ?? 0);
        $storageDef->dataInt3 = (int) ($fieldDef->fieldTypeConstraints->fieldSettings['hideRootTag'] ?? 0);
        $storageDef->dataInt4 = (int) ($fieldDef->fieldTypeConstraints->validators[self::TAGS_VALIDATOR_IDENTIFIER]['maxTags'] ?? 0);
        $storageDef->dataText1 = $fieldDef->fieldTypeConstraints->fieldSettings['editView'] ?? Type::EDIT_VIEW_DEFAULT_VALUE;
    }

    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef): void
    {
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'hideRootTag' => (bool) $storageDef->dataInt3,
                'editView' => $storageDef->dataText1,
            ],
            FieldSettings::ARRAY_AS_PROPS,
        );

        $fieldDef->fieldTypeConstraints->validators = [
            self::TAGS_VALIDATOR_IDENTIFIER => [
                'subTreeLimit' => (int) $storageDef->dataInt1,
                'maxTags' => (int) $storageDef->dataInt4,
            ],
        ];
    }

    public function getIndexColumn(): bool
    {
        return false;
    }
}
