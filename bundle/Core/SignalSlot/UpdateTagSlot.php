<?php

namespace Netgen\TagsBundle\Core\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\UpdateTagSignal;

class UpdateTagSlot extends AbstractPublishSlot
{
    /**
     * @param UpdateTagSignal $signal
     *
     * @return Signal|mixed
     */
    protected function getNgTagId(Signal $signal)
    {
        return $signal->tagId;
    }

    /**
     * @param Signal $signal
     *
     * @return bool
     */
    protected function supports(Signal $signal)
    {
        return $signal instanceof UpdateTagSignal;
    }
}
