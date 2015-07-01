<?php

namespace Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService;

use eZ\Publish\Core\SignalSlot\Signal;

class UpdateTagSignal extends Signal
{
    /**
     * Tag ID
     *
     * @var mixed
     */
    public $tagId;

    /**
     * Tag keywords in the available languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" )
     *
     * @var string[]
     */
    public $keywords;

    /**
     * Remote ID
     *
     * @var string
     */
    public $remoteId;
}
