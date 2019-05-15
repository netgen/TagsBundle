<?php

namespace Netgen\TagsBundle\Tests\Core\SignalSlot;

use Netgen\TagsBundle\Core\SignalSlot\MergeTagsSlot;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\MergeTagsSignal;

class MergeTagsSlotTest extends AbstractPublishSlotTest
{
    public function getSlotClass()
    {
        return MergeTagsSlot::class;
    }

    public function createSignal()
    {
        return new MergeTagsSignal(
            [
                'targetTagId' => $this->tagId,
            ]
        );
    }

    public function getReceivedSignalClasses()
    {
        return [MergeTagsSignal::class];
    }
}
