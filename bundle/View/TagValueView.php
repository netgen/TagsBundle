<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\View;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

interface TagValueView
{
    /**
     * Returns the tag.
     */
    public function getTag(): Tag;
}
