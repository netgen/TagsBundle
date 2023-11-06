<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Values;

use Ibexa\Rest\Value;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class RestTag extends Value
{
    public function __construct(public Tag $tag, public int $childrenCount, public int $synonymsCount) {}
}
