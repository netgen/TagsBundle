<?php

namespace Netgen\TagsBundle;

use Netgen\TagsBundle\API\Repository\TagsService;

abstract class TagsServiceAware implements TagsServiceAwareInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * Sets the tags service.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function setTagsService(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }
}
