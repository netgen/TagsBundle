<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\SPI\Persistence\Tags;

use Ibexa\Contracts\Core\Persistence\ValueObject;

final class SearchResult extends ValueObject
{
    /**
     * @var \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public array $tags;

    public int $totalCount;
}
