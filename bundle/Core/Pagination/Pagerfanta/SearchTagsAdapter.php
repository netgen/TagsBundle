<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

use Netgen\TagsBundle\API\Repository\TagsService;
use Pagerfanta\Adapter\AdapterInterface;

final class SearchTagsAdapter implements AdapterInterface
{
    private string $searchText;

    private string $language;

    private int $nbResults;

    public function __construct(private TagsService $tagsService)
    {
    }

    public function setSearchText(string $searchText): void
    {
        $this->searchText = $searchText;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function getNbResults(): int
    {
        if (!isset($this->nbResults)) {
            $this->getSlice(0, 1);
        }

        return $this->nbResults;
    }

    public function getSlice($offset, $length): iterable
    {
        $searchResult = $this->tagsService->searchTags($this->searchText, $this->language, true, $offset, $length === 0 ? -1 : $length);
        $this->nbResults = $searchResult->totalCount;

        return $searchResult->tags;
    }
}
