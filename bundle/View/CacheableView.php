<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\View;

interface CacheableView
{
    /**
     * Sets the cache as enabled/disabled.
     */
    public function setCacheEnabled($cacheEnabled): void;

    /**
     * Indicates if cache is enabled or not.
     */
    public function isCacheEnabled(): bool;
}
