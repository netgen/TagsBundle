<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\SPI\Persistence\Tags;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * This class represents a value for creating a synonym.
 */
final class SynonymCreateStruct extends ValueObject
{
    /**
     * The ID of the main tag for which the new synonym should be created.
     *
     * @required
     *
     * @var int
     */
    public $mainTagId;

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
     * @var string|null
     */
    public $remoteId;

    /**
     * Indicates if the tag is shown in the main language if it's not present in an other requested language.
     *
     * @var bool|null
     */
    public $alwaysAvailable;
}
