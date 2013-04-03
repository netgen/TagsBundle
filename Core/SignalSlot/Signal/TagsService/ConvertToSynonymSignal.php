<?php

namespace Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService;

use eZ\Publish\Core\SignalSlot\Signal;

class ConvertToSynonymSignal extends Signal
{
    /**
     * Tag ID
     *
     * @var mixed
     */
    public $tagId;

    /**
     * Main tag ID
     *
     * @var mixed
     */
    public $mainTagId;
}
