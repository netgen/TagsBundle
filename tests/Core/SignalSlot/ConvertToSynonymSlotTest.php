<?php

namespace Netgen\TagsBundle\Tests\Core\SignalSlot;

use Netgen\TagsBundle\Core\SignalSlot\ConvertToSynonymSlot;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\ConvertToSynonymSignal;

class ConvertToSynonymSlotTest extends AbstractPublishSlotTest
{
    public function getSlotClass()
    {
        return ConvertToSynonymSlot::class;
    }

    public function createSignal()
    {
        return new ConvertToSynonymSignal(
            [
                'tagId' => $this->tagId,
            ]
        );
    }

    public function getReceivedSignalClasses()
    {
        return [ConvertToSynonymSignal::class];
    }
}
