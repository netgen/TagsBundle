<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use function array_map;
use function implode;

/**
 * Value for Tags field type.
 */
final class Value extends BaseValue
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
     */
    public function __toString(): string
    {
        return implode(
            ', ',
            array_map(
                static function (Tag $tag): string {
                    return $tag->getKeyword() ?? '';
                },
                $this->tags
            )
        );
    }
}
