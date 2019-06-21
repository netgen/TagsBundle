<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Values;

use EzSystems\EzPlatformRest\Value;

final class ContentList extends Value
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
     * @param \EzSystems\EzPlatformRest\Server\Values\RestContent[] $contents
     * @param string $path
     */
    public function __construct(array $contents, string $path)
    {
        $this->contents = $contents;
        $this->path = $path;
    }
}
