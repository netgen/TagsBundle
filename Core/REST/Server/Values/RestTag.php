<?php

namespace Netgen\TagsBundle\Core\REST\Server\Values;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use eZ\Publish\Core\REST\Common\Value;

class RestTag extends Value
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public $tag;

    /**
     * @var int
     */
    public $childCount;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param int $childCount
     */
    public function __construct(Tag $tag, $childCount)
    {
        $this->tag = $tag;
        $this->childCount = $childCount;
    }
}
