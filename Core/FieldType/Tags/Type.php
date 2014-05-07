<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
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
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value $value
     *
     * @return string
     */
    public function getName( SPIValue $value )
    {
        return implode(
            ", ",
            array_map(
                function ( Tag $tag )
                {
                    return $tag->keyword;
                },
                $value->tags
            )
        );
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
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param mixed $inputValue
     *
     * @return \Netgen\TagsBundle\Core\FieldType\Tags\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput( $inputValue )
    {
        if ( is_array( $inputValue ) )
        {
            foreach ( $inputValue as $inputValueItem )
            {
                if ( !$inputValueItem instanceof Tag )
                {
                    return $inputValue;
                }
            }

            $inputValue = new Value( $inputValue );
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value $value
     */
    protected function checkValueStructure( BaseValue $value )
    {
        if ( !is_array( $value->tags ) )
        {
            throw new InvalidArgumentType(
                '$value->tags',
                'array',
                $value->tags
            );
        }

        foreach ( $value->tags as $tag )
        {
            if ( !$tag instanceof Tag )
            {
                throw new InvalidArgumentType(
                    "$tag",
                    "Netgen\\TagsBundle\\Core\\FieldType\\Tags\\Value",
                    $tag
                );
            }
        }
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \Netgen\TagsBundle\Core\FieldType\Tags\Value
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
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value $value
     *
     * @return array
     */
    public function toHash( SPIValue $value )
    {
        $hash = array();

        foreach ( $value->tags as $tag )
        {
            $hash[] = array(
                "id" => $tag->id,
                "parent_id" => $tag->parentTagId,
                "main_tag_id" => $tag->mainTagId,
                "keyword" => $tag->keyword,
                "depth" => $tag->depth,
                "path_string" => $tag->pathString,
                "modified" => $tag->modificationDate->getTimestamp(),
                "remote_id" => $tag->remoteId
            );
        }

        return $hash;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value $value
     *
     * @return bool
     */
    protected function getSortInfo( BaseValue $value )
    {
        return false;
    }

    /**
     * Converts a $value to a persistence value
     *
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue( SPIValue $value )
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
     * @return \Netgen\TagsBundle\Core\FieldType\Tags\Value
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        return $this->fromHash( $fieldValue->externalData );
    }

    /**
     * Returns if the given $value is considered empty by the field type
     *
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value $value
     *
     * @return boolean
     */
    public function isEmptyValue( SPIValue $value )
    {
        return $value === null || $value->tags == $this->getEmptyValue()->tags;
    }
}
