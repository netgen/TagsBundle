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
    public $childrenCount;

    /**
     * @var int
     */
    public $synonymsCount;

    /**
     * @var int
     */
    public $relatedContentCount;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param int $childrenCount
     * @param int $synonymsCount
     * @param int $relatedContentCount
     */
    public function __construct(Tag $tag, $childrenCount, $synonymsCount, $relatedContentCount)
    {
        $this->tag = $tag;
        $this->childrenCount = $childrenCount;
        $this->synonymsCount = $synonymsCount;
        $this->relatedContentCount = $relatedContentCount;
    }
}
