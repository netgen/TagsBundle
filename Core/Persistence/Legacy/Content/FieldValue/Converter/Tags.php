<?php

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\FieldType\FieldSettings;
use Netgen\TagsBundle\Core\FieldType\Tags\Type;

/**
 * Converter for Tags field values in legacy storage.
 */
class Tags implements Converter
{
    /**
     * Factory for current class.
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
     * @return \Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Converts data from $value to $storageFieldValue.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
    }

    /**
     * Converts data from $value to $fieldValue.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        $storageDef->dataInt1 = isset($fieldDef->fieldTypeConstraints->fieldSettings['subTreeLimit']) ?
            (int)$fieldDef->fieldTypeConstraints->fieldSettings['subTreeLimit'] :
            0;

        $storageDef->dataInt3 = isset($fieldDef->fieldTypeConstraints->fieldSettings['hideRootTag']) ?
            (int)$fieldDef->fieldTypeConstraints->fieldSettings['hideRootTag'] :
            0;

        $storageDef->dataInt4 = isset($fieldDef->fieldTypeConstraints->fieldSettings['maxTags']) ?
            (int)$fieldDef->fieldTypeConstraints->fieldSettings['maxTags'] :
            0;

        $storageDef->dataText1 = isset($fieldDef->fieldTypeConstraints->fieldSettings['editView']) ?
            $fieldDef->fieldTypeConstraints->fieldSettings['editView'] :
            Type::EDIT_VIEW_DEFAULT_VALUE;
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'subTreeLimit' => (int)$storageDef->dataInt1,
                'hideRootTag' => (bool)$storageDef->dataInt3,
                'maxTags' => (int)$storageDef->dataInt4,
                'editView' => $storageDef->dataText1,
            )
        );
    }

    /**
     * Returns the name of the index column in the attribute table.
     *
     * @return string
     */
    public function getIndexColumn()
    {
        return false;
    }
}
