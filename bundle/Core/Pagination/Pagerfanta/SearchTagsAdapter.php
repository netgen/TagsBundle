<?php

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

use Netgen\TagsBundle\API\Repository\TagsService;
use Pagerfanta\Adapter\AdapterInterface;

class SearchTagsAdapter implements AdapterInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var string
     */
    protected $searchText;

    /**
     * @var string
     */
    protected $language;

    /**
     * @var int
     */
    protected $nbResults;

    /**
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * @param string $searchText
     */
    public function setSearchText($searchText)
    {
        $this->searchText = $searchText;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Returns the number of results.
     *
     * @return int The number of results
     */
    public function getNbResults()
    {
        if ($this->nbResults === null) {
            $this->getSlice(0, 1);
        }

        return $this->nbResults;
    }

    /**
     * Returns an slice of the results.
     *
     * @param int $offset The offset
     * @param int $length The length
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public function getSlice($offset, $length)
    {
        $searchResult = $this->tagsService->searchTags($this->searchText, $this->language, true, $offset, $length === 0 ? -1 : $length);
        $this->nbResults = $searchResult->totalCount;

        return $searchResult->tags;
    }
}
