<?php

namespace Netgen\TagsBundle\Core\REST\Values;

use EzSystems\EzPlatformRest\Value;

class CreatedTag extends Value
{
    /**
     * @var \Netgen\TagsBundle\Core\REST\Values\RestTag
     */
    public $restTag;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\Core\REST\Values\RestTag $restTag
     */
    public function __construct(RestTag $restTag)
    {
        $this->restTag = $restTag;
    }
}
