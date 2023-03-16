<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Pagerfanta\Adapter\AdapterInterface;

final class ChildrenTagsAdapter implements AdapterInterface, TagAdapterInterface
{
    private ?Tag $tag = null;

    private int $nbResults;

    public function __construct(private TagsService $tagsService)
    {
    }

    public function setTag(Tag $tag): void
    {
        $this->tag = $tag;
    }

    public function getNbResults(): int
    {
        $this->nbResults = $this->nbResults ?? $this->tagsService->getTagChildrenCount($this->tag);

        return $this->nbResults;
    }

    public function getSlice($offset, $length): iterable
    {
        $childrenTags = $this->tagsService->loadTagChildren($this->tag, $offset, $length);

        $this->nbResults = $this->nbResults ?? $this->tagsService->getTagChildrenCount($this->tag);

        return $childrenTags;
    }
}
