<?php

namespace Netgen\TagsBundle\Tests\Core\SignalSlot;

use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\UpdateTagSignal;
use Netgen\TagsBundle\Core\SignalSlot\UpdateTagSlot;

class UpdateTagSlotTest extends AbstractPublishSlotTest
{
    public function getSlotClass()
    {
        return UpdateTagSlot::class;
    }

    public function createSignal()
    {
        return new UpdateTagSignal(
            [
                'tagId' => $this->tagId,
            ]
        );
    }

    public function getReceivedSignalClasses()
    {
        return [UpdateTagSignal::class];
    }
}
