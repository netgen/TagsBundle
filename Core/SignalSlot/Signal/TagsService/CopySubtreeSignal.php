<?php

namespace Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService;

use eZ\Publish\Core\SignalSlot\Signal;

class CopySubtreeSignal extends Signal
{
    /**
     * Source tag ID
     *
     * @var mixed
     */
    public $sourceTagId;

    /**
     * Target parent tag ID
     *
     * @var mixed
     */
    public $targetParentTagId;

    /**
     * New tag ID
     *
     * @var mixed
     */
    public $newTagId;
}
