<?php

namespace EzSystems\TagsBundle\Core\FieldType\Tags;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use EzSystems\TagsBundle\API\Repository\Values\Tags\Tag;

/**
 * Value for Tags field type
 */
class Value extends BaseValue
{
    /**
     * @var \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public $tags = array();

    /**
     * Constructor
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag[] $tags
     */
    public function __construct( array $tags = array() )
    {
        // @TODO: Use TagCollection instead of plain array
        $this->tags = $tags;
    }

    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString()
    {
        return implode(
            ", ",
            array_map(
                function ( Tag $tag )
                {
                    return $tag->keyword;
                },
                $this->tags
            )
        );
    }
}
