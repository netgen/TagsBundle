<?php

namespace Netgen\TagsBundle\Core\REST\Values;

use EzSystems\EzPlatformRest\Value;

class TagList extends Value
{
    /**
     * @var \Netgen\TagsBundle\Core\REST\Values\RestTag[]
     */
    public $tags;

    /**
     * @var string
     */
    public $path;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\Core\REST\Values\RestTag[] $tags
     * @param string $path
     */
    public function __construct(array $tags, $path)
    {
        $this->tags = $tags;
        $this->path = $path;
    }
}
