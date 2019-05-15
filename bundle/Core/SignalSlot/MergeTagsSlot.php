<?php

namespace Netgen\TagsBundle\Core\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\MergeTagsSignal;

class MergeTagsSlot extends AbstractPublishSlot
{
    /**
     * @param MergeTagsSignal $signal
     *
     * @return Signal|mixed
     */
    protected function getNgTagId(Signal $signal)
    {
        return $signal->targetTagId;
    }

    /**
     * @param Signal $signal
     *
     * @return bool
     */
    protected function supports(Signal $signal)
    {
        return $signal instanceof MergeTagsSignal;
    }
}
