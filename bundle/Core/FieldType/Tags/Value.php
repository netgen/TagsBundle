<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use Ibexa\Core\FieldType\Value as BaseValue;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Stringable;

use function array_map;
use function implode;

/**
 * Value for Tags field type.
 */
final class Value extends BaseValue implements Stringable
{
    /**
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[] $tags
     */
    public function __construct(public array $tags = []) {}

    /**
     * Returns a string representation of the field value.
     */
    public function __toString(): string
    {
        return implode(
            ', ',
            array_map(
                static fn (Tag $tag): string => $tag->getKeyword() ?? '',
                $this->tags,
            ),
        );
    }
}
