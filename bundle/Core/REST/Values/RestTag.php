<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Values;

use EzSystems\EzPlatformRest\Value;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class RestTag extends Value
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public $tag;

    /**
     * @var int
     */
    public $childrenCount;

    /**
     * @var int
     */
    public $synonymsCount;

    public function __construct(Tag $tag, int $childrenCount, int $synonymsCount)
    {
        $this->tag = $tag;
        $this->childrenCount = $childrenCount;
        $this->synonymsCount = $synonymsCount;
    }
}
