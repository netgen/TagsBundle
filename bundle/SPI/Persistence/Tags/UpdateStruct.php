<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\SPI\Persistence\Tags;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * This class represents a value for updating a tag.
 */
final class UpdateStruct extends ValueObject
{
    /**
     * Tag keywords in the target languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" ).
     *
     * @var string[]|null
     */
    public $keywords;

    /**
     * A global unique ID of the tag.
     *
     * @var string|null
     */
    public $remoteId;

    /**
     * The main language code for the tag.
     *
     * @var string|null
     */
    public $mainLanguageCode;

    /**
     * Indicates if the tag is shown in the main language if it's not present in an other requested language.
     *
     * @var bool|null
     */
    public $alwaysAvailable;
}
