<?php

namespace Netgen\TagsBundle\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value;

class ContentList extends Value
{
    /**
     * @var \eZ\Publish\Core\REST\Server\Values\RestContent[]
     */
    public $contents;

    /**
     * @var string
     */
    public $path;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\Core\REST\Server\Values\RestContent[] $contents
     * @param string $path
     */
    public function __construct(array $contents, $path)
    {
        $this->contents = $contents;
        $this->path = $path;
    }
}
