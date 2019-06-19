<?php

namespace Netgen\TagsBundle\Core\REST\Values;

use EzSystems\EzPlatformRest\Value;

class CreatedTag extends Value
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
