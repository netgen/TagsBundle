<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService;

use eZ\Publish\Core\SignalSlot\Signal;

class ConvertToSynonymSignal extends Signal
{
    /**
     * Tag ID.
     *
     * @var int
     */
    public $tagId;

    /**
     * Main tag ID.
     *
     * @var int
     */
    public $mainTagId;
}
