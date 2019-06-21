<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService;

use eZ\Publish\Core\SignalSlot\Signal;

final class CreateTagSignal extends Signal
{
    /**
     * Tag ID.
     *
     * @var int
     */
    public $tagId;

    /**
     * Parent tag ID.
     *
     * @var int
     */
    public $parentTagId;

    /**
     * Tag keywords in the available languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" ).
     *
     * @var string[]
     */
    public $keywords;

    /**
     * The main language code for the tag.
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * Indicates if the tag is shown in the main language if it's not present in an other requested language.
     *
     * @var bool
     */
    public $alwaysAvailable;
}
