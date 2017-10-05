<?php

namespace Netgen\TagsBundle\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value;

class TagList extends Value
{
    /**
     * @var \Netgen\TagsBundle\Core\REST\Server\Values\RestTag[]
     */
    public $tags;

    /**
     * @var string
     */
    public $path;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\Core\REST\Server\Values\RestTag[] $tags
     * @param string $path
     */
    public function __construct(array $tags, $path)
    {
        $this->tags = $tags;
        $this->path = $path;
    }
}
