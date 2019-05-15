<?php

namespace Netgen\TagsBundle\Tests\Core\SignalSlot;

use Netgen\TagsBundle\Core\SignalSlot\DeleteTagSlot;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\DeleteTagSignal;

class DeleteTagSlotTest extends AbstractPublishSlotTest
{
    public function getSlotClass()
    {
        return DeleteTagSlot::class;
    }

    public function createSignal()
    {
        return new DeleteTagSignal(
            [
                'tagId' => $this->tagId,
            ]
        );
    }

    public function getReceivedSignalClasses()
    {
        return [DeleteTagSignal::class];
    }
}
