<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

use Netgen\TagsBundle\API\Repository\TagsService;
use Pagerfanta\Adapter\AdapterInterface;

final class SearchTagsAdapter implements AdapterInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var string
     */
    private $searchText;

    /**
     * @var string
     */
    private $language;

    /**
     * @var int
     */
    private $nbResults;

    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
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
        if ($this->nbResults === null) {
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
