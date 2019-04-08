<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

/**
 * Value for Tags field type.
 */
class Value extends BaseValue
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public $tags = [];

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[] $tags
     */
    public function __construct(array $tags = [])
    {
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
            ', ',
            array_map(
                static function (Tag $tag) {
                    return $tag->getKeyword();
                },
                $this->tags
            )
        );
    }
}
