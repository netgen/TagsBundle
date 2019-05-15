<?php

namespace Netgen\TagsBundle\Core\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\CopySubtreeSignal;

class CopySubtreeSlot extends AbstractPublishSlot
{
    /**
     * @param CopySubtreeSignal $signal
     *
     * @return Signal|mixed
     */
    protected function getNgTagId(Signal $signal)
    {
        return $signal->newTagId;
    }

    /**
     * @param Signal $signal
     *
     * @return bool
     */
    protected function supports(Signal $signal)
    {
        return $signal instanceof CopySubtreeSignal;
    }
}
