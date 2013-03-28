<?php

namespace EzSystems\TagsBundle\SPI\Persistence\Tags;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * This class represents a value for updating a tag
 */
class UpdateStruct extends ValueObject
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
