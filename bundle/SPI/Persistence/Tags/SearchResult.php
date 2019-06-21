<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\SPI\Persistence\Tags;

use eZ\Publish\SPI\Persistence\ValueObject;

class SearchResult extends ValueObject
{
    /**
     * @var \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public $tags;

    /**
     * @var int
     */
    public $totalCount;
}
