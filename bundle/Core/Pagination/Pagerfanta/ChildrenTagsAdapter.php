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
    private ?string $sortBy = null;
    private ?string $sortOrder = null;

    public function __construct(private readonly TagsService $tagsService) {}

    public function setTag(Tag $tag): void
    {
        $this->tag = $tag;
    }

    public function setSorting(?string $sortBy, ?string $sortOrder): void
    {
        $this->sortBy = $sortBy;
        $this->sortOrder = $sortOrder;
    }

    public function getNbResults(): int
    {
        $this->nbResults = $this->nbResults ?? $this->tagsService->getTagChildrenCount($this->tag);

        return $this->nbResults;
    }

    public function getSlice($offset, $length): iterable
    {
        $childrenTags = $this->tagsService->loadTagChildren(
            $this->tag,
            $offset,
            $length,
            null,
            true,
            $this->sortBy,
            $this->sortOrder,
        );

        $this->nbResults = $this->nbResults ?? $this->tagsService->getTagChildrenCount($this->tag);

        return $childrenTags;
    }
}
