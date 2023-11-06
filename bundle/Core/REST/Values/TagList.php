<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Values;

use Ibexa\Rest\Value;

final class TagList extends Value
{
    /**
     * @param \Netgen\TagsBundle\Core\REST\Values\RestTag[] $tags
     */
    public function __construct(public array $tags, public string $path) {}
}
