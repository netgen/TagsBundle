<?php

namespace EzSystems\TagsBundle\SPI\Persistence\Tags;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * Class representing a tag
 */
class Tag extends ValueObject
{
    /**
     * Tag ID
     *
     * @var mixed
     */
    public $id;

    /**
     * Parent tag ID
     *
     * @var mixed
     */
    public $parentTagId;

    /**
     * Main tag ID
     *
     * Zero if tag is not a synonym
     *
     * @var mixed
     */
    public $mainTagId;

    /**
     * Tag keyword
     *
     * @var string
     */
    public $keyword;

    /**
     * The depth tag has in tag tree
     *
     * @var int
     */
    public $depth;

    /**
     * The path to this tag e.g. /1/6/21/42 where 42 is the current ID
     *
     * @var string
     */
    public $pathString;

    /**
     * Tag modification date as a UNIX timestamp
     *
     * @var int
     */
    public $modificationDate;

    /**
     * A global unique ID of the tag
     *
     * @var string
     */
    public $remoteId;
}
