<?php

namespace Netgen\TagsBundle;

use Netgen\TagsBundle\API\Repository\TagsService;

trait TagsServiceAwareTrait
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    public function setTagsService(TagsService $tagsService): void
    {
        $this->tagsService = $tagsService;
    }
}
