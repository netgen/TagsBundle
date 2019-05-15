<?php

namespace Netgen\TagsBundle\Tests\Core\SignalSlot;

use Netgen\TagsBundle\Core\SignalSlot\CopySubtreeSlot;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\CopySubtreeSignal;

class CopySubtreeSlotTest extends AbstractPublishSlotTest
{
    public function getSlotClass()
    {
        return CopySubtreeSlot::class;
    }

    public function createSignal()
    {
        return new CopySubtreeSignal(
            [
                'newTagId' => $this->tagId,
            ]
        );
    }

    public function getReceivedSignalClasses()
    {
        return [CopySubtreeSignal::class];
    }
}
