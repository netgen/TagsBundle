<?php

namespace Netgen\TagsBundle\API\Repository\Values\Tags;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a value for updating a tag
 */
class TagUpdateStruct extends ValueObject
{
    /**
     * Tag keywords in the target languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" )
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

    /**
     * The main language code for the tag
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * Indicates if the tag is shown in the main language if it's not present in an other requested language
     *
     * @var boolean
     */
    public $alwaysAvailable;
}
