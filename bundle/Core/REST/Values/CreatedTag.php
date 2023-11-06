<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Values;

use Ibexa\Rest\Value;

final class CreatedTag extends Value
{
    public function __construct(public RestTag $restTag) {}
}
