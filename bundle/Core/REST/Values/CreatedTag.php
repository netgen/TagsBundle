<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Values;

use Ibexa\Rest\Value;

final class CreatedTag extends Value
{
    /**
     * @var \Netgen\TagsBundle\Core\REST\Values\RestTag
     */
    public $restTag;

    public function __construct(RestTag $restTag)
    {
        $this->restTag = $restTag;
    }
}
