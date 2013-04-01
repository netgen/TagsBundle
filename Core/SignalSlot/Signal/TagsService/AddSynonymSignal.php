<?php

namespace EzSystems\TagsBundle\Core\SignalSlot\Signal\TagsService;

use eZ\Publish\Core\SignalSlot\Signal;

class AddSynonymSignal extends Signal
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

    /**
     * Tag keyword
     *
     * @var string
     */
    public $keyword;
}
