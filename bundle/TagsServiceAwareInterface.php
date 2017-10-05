<?php

namespace Netgen\TagsBundle;

use Netgen\TagsBundle\API\Repository\TagsService;

interface TagsServiceAwareInterface
{
    /**
     * Sets the tags service.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function setTagsService(TagsService $tagsService);
}
