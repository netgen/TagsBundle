<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService;

use eZ\Publish\Core\SignalSlot\Signal;

class MergeTagsSignal extends Signal
{
    /**
     * Tag ID.
     *
     * @var int
     */
    public $tagId;

    /**
     * Target tag ID.
     *
     * @var int
     */
    public $targetTagId;
}
