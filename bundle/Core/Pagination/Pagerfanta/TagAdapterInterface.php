<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

interface TagAdapterInterface
{
    /**
     * Sets the tag to the adapter.
     */
    public function setTag(Tag $tag): void;
}
