<?php

namespace Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService;

use eZ\Publish\Core\SignalSlot\Signal;

class MergeTagsSignal extends Signal
{
    /**
     * Tag ID
     *
     * @var mixed
     */
    public $tagId;

    /**
     * Target tag ID
     *
     * @var mixed
     */
    public $targetTagId;
}
