<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Values;

use Ibexa\Rest\Value;

final class ContentList extends Value
{
    /**
     * @var \Ibexa\Rest\Server\Values\RestContent[]
     */
    public $contents;

    /**
     * @var string
     */
    public $path;

    /**
     * @param \Ibexa\Rest\Server\Values\RestContent[] $contents
     * @param string $path
     */
    public function __construct(array $contents, string $path)
    {
        $this->contents = $contents;
        $this->path = $path;
    }
}
