<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\SPI\Persistence\Tags;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * This class represents a value for creating a tag.
 */
class CreateStruct extends ValueObject
{
    /**
     * The ID of the parent tag under which the new tag should be created.
     *
     * @required
     *
     * @var int
     */
    public $parentTagId;

    /**
     * The main language code for the tag.
     *
     * @required
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * Tag keywords in the target languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" ).
     *
     * @required
     *
     * @var string[]
     */
    public $keywords;

    /**
     * A global unique ID of the tag.
     *
     * @var string
     */
    public $remoteId;

    /**
     * Indicates if the tag is shown in the main language if it's not present in an other requested language.
     *
     * @var bool
     */
    public $alwaysAvailable;
}
