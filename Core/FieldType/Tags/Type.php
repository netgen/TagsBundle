<?php

namespace EzSystems\TagsBundle\Core\FieldType\Tags;

use eZ\Publish\Core\FieldType\FieldType;
use EzSystems\TagsBundle\Core\FieldType\Tags\Value;

/**
 * Tags field type
 *
 * Represents tags.
 */
class Type extends FieldType
{
    /**
     * Returns the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "eztags";
    }

    /**
     * Returns a human readable string representation from the given $value
     *
     * @param mixed $value
     *
     * @return string
     */
    public function getName( $value )
    {
        // TODO: Implement getName() method.
    }

    /**
     * Returns the empty value for this field type.
     *
     * @return \EzSystems\TagsBundle\Core\FieldType\Tags\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return mixed
     */
    public function fromHash( $hash )
    {
        // TODO: Implement fromHash() method.
    }

    /**
     * Converts the given $value into a plain hash format
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        // TODO: Implement toHash() method.
    }

    /**
     * Implements the core of {@see acceptValue()}.
     *
     * @param mixed $inputValue
     *
     * @return \EzSystems\TagsBundle\Core\FieldType\Tags\Value The potentially converted and structurally plausible value.
     */
    protected function internalAcceptValue( $inputValue )
    {
        // TODO: Implement internalAcceptValue() method.
    }
}
