<?php

namespace Netgen\TagsBundle\Tests\Core\SignalSlot;

use Netgen\TagsBundle\Core\SignalSlot\CreateTagSlot;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\CreateTagSignal;

class CreateTagSlotTest extends AbstractPublishSlotTest
{
    public function getSlotClass()
    {
        return CreateTagSlot::class;
    }

    public function createSignal()
    {
        return new CreateTagSignal(
            [
                'tagId' => $this->tagId,
            ]
        );
    }

    public function getReceivedSignalClasses()
    {
        return [CreateTagSignal::class];
    }
}
