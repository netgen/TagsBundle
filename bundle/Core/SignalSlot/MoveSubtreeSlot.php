<?php

namespace Netgen\TagsBundle\Core\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\MoveSubtreeSignal;

class MoveSubtreeSlot extends AbstractPublishSlot
{
    /**
     * @param MoveSubtreeSignal $signal
     *
     * @return Signal|mixed
     */
    protected function getNgTagId(Signal $signal)
    {
        return $signal->targetParentTagId;
    }

    /**
     * @param Signal $signal
     *
     * @return bool
     */
    protected function supports(Signal $signal)
    {
        return $signal instanceof MoveSubtreeSignal;
    }
}
