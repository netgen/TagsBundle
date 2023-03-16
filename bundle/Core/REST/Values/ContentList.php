<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Values;

use Ibexa\Rest\Value;

final class ContentList extends Value
{
    /**
     * @param \Ibexa\Rest\Server\Values\RestContent[] $contents
     */
    public function __construct(public array $contents, public string $path)
    {
    }
}
