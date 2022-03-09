<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Values;

use Ibexa\Rest\Value;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class RestTag extends Value
{
    public Tag $tag;

    public int $childrenCount;

    public int $synonymsCount;

    public function __construct(Tag $tag, int $childrenCount, int $synonymsCount)
    {
        $this->tag = $tag;
        $this->childrenCount = $childrenCount;
        $this->synonymsCount = $synonymsCount;
    }
}
