<?php

namespace Netgen\TagsBundle\Core\REST\Values;

use EzSystems\EzPlatformRest\Value;

class ContentList extends Value
{
    /**
     * @var \EzSystems\EzPlatformRest\Server\Values\RestContent[]
     */
    public $contents;

    /**
     * @var string
     */
    public $path;

    /**
     * Constructor.
     *
     * @param \EzSystems\EzPlatformRest\Server\Values\RestContent[] $contents
     * @param string $path
     */
    public function __construct(array $contents, $path)
    {
        $this->contents = $contents;
        $this->path = $path;
    }
}
