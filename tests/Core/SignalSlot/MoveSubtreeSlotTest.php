<?php

namespace Netgen\TagsBundle\Tests\Core\SignalSlot;

use Netgen\TagsBundle\Core\SignalSlot\MoveSubtreeSlot;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\MoveSubtreeSignal;

class MoveSubtreeSlotTest extends AbstractPublishSlotTest
{
    public function getSlotClass()
    {
        return MoveSubtreeSlot::class;
    }

    public function createSignal()
    {
        return new MoveSubtreeSignal(
            [
                'targetParentTagId' => $this->tagId,
            ]
        );
    }

    public function getReceivedSignalClasses()
    {
        return [MoveSubtreeSignal::class];
    }
}
