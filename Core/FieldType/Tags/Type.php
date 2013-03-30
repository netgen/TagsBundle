<?php

namespace EzSystems\TagsBundle\Core\FieldType\Tags;

use EzSystems\TagsBundle\API\Repository\Values\Tags\Tag;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use EzSystems\TagsBundle\Core\FieldType\Tags\Value;
use DateTime;

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
        if ( !is_array( $hash ) )
        {
            return new Value();
        }

        $tags = array();

        foreach ( $hash as $hashItem )
        {
            if ( !is_array( $hashItem ) )
            {
                continue;
            }

            $modificationDate = new DateTime();
            $modificationDate->setTimestamp( $hashItem["modified"] );

            $tags[] = new Tag(
                array(
                     "id" => $hashItem["id"],
                     "parentTagId" => $hashItem["parent_id"],
                     "mainTagId" => $hashItem["main_tag_id"],
                     "keyword" => $hashItem["keyword"],
                     "depth" => $hashItem["depth"],
                     "pathString" => $hashItem["path_string"],
                     "modificationDate" => $modificationDate,
                     "remoteId" => $hashItem["remote_id"]
                )
            );
        }

        return new Value( $tags );
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

    /**
     * Converts a $value to a persistence value
     *
     * @param mixed $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue( $value )
    {
        return new FieldValue(
            array(
                "data" => null,
                "externalData" => $this->toHash( $value ),
                "sortKey" => $this->getSortInfo( $value ),
            )
        );
    }

    /**
     * Converts a persistence $fieldValue to a Value
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return mixed
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        return $this->fromHash( $fieldValue->externalData );
    }
}
