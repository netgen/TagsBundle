<?php

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Pagerfanta\Adapter\AdapterInterface;

class ChildrenTagsAdapter implements AdapterInterface, TagAdapterInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var int
     */
    private $nbResults;

    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    public function setTag(Tag $tag): void
    {
        $this->tag = $tag;
    }

    public function getNbResults(): int
    {
        if (!isset($this->nbResults)) {
            $this->nbResults = $this->tagsService->getTagChildrenCount($this->tag);
        }

        return $this->nbResults;
    }

    public function getSlice($offset, $length): iterable
    {
        $childrenTags = $this->tagsService->loadTagChildren($this->tag, $offset, $length);

        if (!isset($this->nbResults)) {
            $this->nbResults = $this->tagsService->getTagChildrenCount($this->tag);
        }

        return $childrenTags;
    }
}
