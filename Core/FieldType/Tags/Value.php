<?php

namespace EzSystems\TagsBundle\Core\FieldType\Tags;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use EzSystems\TagsBundle\Core\FieldType\Tags\Value\Tag;

/**
 * Value for Tags field type
 */
class Value extends BaseValue
{
    /**
     * @var \EzSystems\TagsBundle\Core\FieldType\Tags\Value\Tag[]
     */
    public $tags = array();

    /**
     * Constructor
     *
     * @param \EzSystems\TagsBundle\Core\FieldType\Tags\Value\Tag[] $tags
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
                    return $tag->getKeyword();
                },
                $this->tags
            )
        );
    }
}
