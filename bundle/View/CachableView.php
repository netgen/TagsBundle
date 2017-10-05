<?php

namespace Netgen\TagsBundle\View;

interface CachableView
{
    /**
     * Sets the cache as enabled/disabled.
     *
     * @param bool $cacheEnabled
     */
    public function setCacheEnabled($cacheEnabled);

    /**
     * Indicates if cache is enabled or not.
     *
     * @return bool
     */
    public function isCacheEnabled();
}
