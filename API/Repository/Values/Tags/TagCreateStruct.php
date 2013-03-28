<?php

namespace EzSystems\TagsBundle\API\Repository\Values\Tags;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a value for creating a tag
 */
class TagCreateStruct extends ValueObject
{
    /**
     * The ID of the parent tag under which the new tag should be created
     *
     * @required
     *
     * @var mixed
     */
    public $parentTagId;

    /**
     * Tag keyword
     *
     * @required
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
