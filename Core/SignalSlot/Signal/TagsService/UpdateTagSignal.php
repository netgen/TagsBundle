<?php

namespace EzSystems\TagsBundle\Core\SignalSlot\Signal\TagsService;

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
     * Tag keyword
     *
     * @var string
     */
    public $keyword;

    /**
     * Remote ID
     *
     * @var string
     */
    public $remoteId;
}
