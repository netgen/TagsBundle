<?php

namespace Netgen\TagsBundle\SPI\Persistence\Tags;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * This class represents a value for creating a tag
 */
class CreateStruct extends ValueObject
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
     * Tag keywords in the target languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" )
     *
     * @required
     *
     * @var string[]
     */
    public $keywords;

    /**
     * A global unique ID of the tag
     *
     * @var string
     */
    public $remoteId;
}
