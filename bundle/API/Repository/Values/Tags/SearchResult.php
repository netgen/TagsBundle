<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Tags;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * @property-read \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[] $tags Found tags
 * @property-read int $totalCount Total count of the search
 */
final class SearchResult extends ValueObject
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    protected $tags;

    /**
     * @var int
     */
    protected $totalCount;
}
