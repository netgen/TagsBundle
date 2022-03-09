<?php

declare(strict_types=1);

namespace Netgen\TagsBundle;

use Netgen\TagsBundle\API\Repository\TagsService;

trait TagsServiceAwareTrait
{
    protected TagsService $tagsService;

    public function setTagsService(TagsService $tagsService): void
    {
        $this->tagsService = $tagsService;
    }
}
