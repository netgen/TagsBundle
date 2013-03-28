<?php

namespace EzSystems\TagsBundle\API\Repository\Values\Tags;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a value for updating a tag
 */
class TagUpdateStruct extends ValueObject
{
    /**
     * Tag keyword
     *
     * @var string
     */
    public $keyword;

    /**
     * A global unique ID of the tag
     *
     * @var string
     */
    public $remoteId;
}
