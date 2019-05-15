<?php

namespace Netgen\TagsBundle\Tests\Core\SignalSlot;

use Netgen\TagsBundle\Core\SignalSlot\AddSynonymSlot;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\AddSynonymSignal;

class AddSynonymSlotTest extends AbstractPublishSlotTest
{
    public function getSlotClass()
    {
        return AddSynonymSlot::class;
    }

    public function createSignal()
    {
        return new AddSynonymSignal(
            [
                'tagId' => $this->tagId,
            ]
        );
    }

    public function getReceivedSignalClasses()
    {
        return [AddSynonymSignal::class];
    }
}
