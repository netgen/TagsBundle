<?php

namespace Netgen\TagsBundle\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value;

class CreatedTag extends Value
{
    /**
     * @var \Netgen\TagsBundle\Core\REST\Server\Values\RestTag
     */
    public $restTag;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\Core\REST\Server\Values\RestTag $restTag
     */
    public function __construct(RestTag $restTag)
    {
        $this->restTag = $restTag;
    }
}
