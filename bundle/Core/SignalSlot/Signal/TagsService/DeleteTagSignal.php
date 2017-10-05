<?php

namespace Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService;

use eZ\Publish\Core\SignalSlot\Signal;

class DeleteTagSignal extends Signal
{
    /**
     * Tag ID.
     *
     * @var mixed
     */
    public $tagId;
}
