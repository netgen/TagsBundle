<?php

namespace Netgen\TagsBundle;

use Netgen\TagsBundle\API\Repository\TagsService;

interface TagsServiceAwareInterface
{
    /**
     * Sets the tags service.
     */
    public function setTagsService(TagsService $tagsService): void;
}
