<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Tags;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * @property-read \Netgen\TagsBundle\API\Repository\Values\Tags\TagList $tags Found tags
 * @property-read int $totalCount Total count of the search
 */
final class SearchResult extends ValueObject
{
    protected TagList $tags;

    protected int $totalCount;
}
