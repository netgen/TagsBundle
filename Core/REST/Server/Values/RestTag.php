<?php

namespace Netgen\TagsBundle\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value;
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

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param int $childrenCount
     * @param int $synonymsCount
     */
    public function __construct(Tag $tag, $childrenCount, $synonymsCount)
    {
        $this->tag = $tag;
        $this->childrenCount = $childrenCount;
        $this->synonymsCount = $synonymsCount;
    }
}
