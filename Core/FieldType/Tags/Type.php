<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\TagsService;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use DateTime;

/**
 * Tags field type
 *
 * Represents tags.
 */
class Type extends FieldType
{
    /**
     * List of settings available for this FieldType
     *
     * The key is the setting name, and the value is the default value for this setting
     *
     * @var array
     */
    protected $settingsSchema = array(
        "subTreeLimit" => array(
            "type" => "int",
            "default" => 0
        ),
        "showDropDown" => array(
            "type" => "boolean",
            "default" => false
        ),
        "hideRootTag" => array(
            "type" => "boolean",
            "default" => false
        ),
        "maxTags" => array(
            "type" => "int",
            "default" => 0
        )
    );

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct( TagsService $tagsService )
    {
        $this->tagsService = $tagsService;
    }

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

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings( $fieldSettings )
    {
        $validationErrors = array();

        if ( !is_array( $fieldSettings ) )
        {
            $validationErrors[] = new ValidationError( "Field settings must be in form of an array" );

            return $validationErrors;
        }

        foreach ( $fieldSettings as $name => $value )
        {
            if ( !isset( $this->settingsSchema[$name] ) )
            {
                $validationErrors[] = new ValidationError(
                    "Setting '%setting%' is unknown",
                    null,
                    array(
                        "setting" => $name
                    )
                );
                continue;
            }

            switch ( $name )
            {
                case "subTreeLimit":
                    if ( !is_integer( $value ) )
                    {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of integer type",
                            null,
                            array(
                                "setting" => $name
                            )
                        );
                    }

                    if ( $value < 0 )
                    {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be equal or larger than 0",
                            null,
                            array(
                                "setting" => $name
                            )
                        );
                    }

                    if ( $value > 0 )
                    {
                        try
                        {
                            $this->tagsService->loadTag( $value );
                        }
                        catch ( NotFoundException $e )
                        {
                            $validationErrors[] = new ValidationError(
                                "Setting '%setting%' value must be an existing tag ID",
                                null,
                                array(
                                    "setting" => $name
                                )
                            );
                        }
                    }
                    break;
                case "showDropDown":
                    if ( !is_bool( $value ) )
                    {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of boolean type",
                            null,
                            array(
                                "setting" => $name
                            )
                        );
                    }
                    break;
                case "hideRootTag":
                    if ( !is_bool( $value ) )
                    {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of boolean type",
                            null,
                            array(
                                "setting" => $name
                            )
                        );
                    }
                    break;
                case "maxTags":
                    if ( !is_integer( $value ) )
                    {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of integer type",
                            null,
                            array(
                                "setting" => $name
                            )
                        );
                    }

                    if ( $value < 0 )
                    {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be equal or larger than 0",
                            null,
                            array(
                                "setting" => $name
                            )
                        );
                    }
                    break;
            }
        }

        return $validationErrors;
    }
    
    /**
     * Returns whether the field type is searchable
     *
     * @return boolean
     */
    public function isSearchable()
    {
        return true;
    }
}
