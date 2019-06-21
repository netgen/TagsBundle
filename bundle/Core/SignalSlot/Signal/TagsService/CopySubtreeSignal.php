<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService;

use eZ\Publish\Core\SignalSlot\Signal;

class CopySubtreeSignal extends Signal
{
    /**
     * Source tag ID.
     *
     * @var int
     */
    public $sourceTagId;

    /**
     * Target parent tag ID.
     *
     * @var int
     */
    public $targetParentTagId;

    /**
     * New tag ID.
     *
     * @var int
     */
    public $newTagId;
}
