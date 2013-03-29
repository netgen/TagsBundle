<?php

namespace EzSystems\TagsBundle\Core\SignalSlot\Signal\TagsService;

use eZ\Publish\Core\SignalSlot\Signal;

class CreateTagSignal extends Signal
{
    /**
     * Tag ID
     *
     * @var mixed
     */
    public $tagId;

    /**
     * Parent tag ID
     *
     * @var mixed
     */
    public $parentTagId;

    /**
     * Tag keyword
     *
     * @var string
     */
    public $keyword;
}
