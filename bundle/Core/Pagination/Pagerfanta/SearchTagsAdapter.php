<?php

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Pagerfanta\Adapter\AdapterInterface;

class SearchTagsAdapter implements AdapterInterface, SearchTagsAdapterInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var string
     */
    protected $searchTerm;

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
     * @param string $searchTerm
     */
    public function setSearchTerm(string $searchTerm)
    {
        $this->searchTerm = $searchTerm;
    }

    /**
     * @param string $language
     */
    public function setLanguage(string $language)
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
            $this->getSlice(0, 0);
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
        try {
            $searchResult = $this->tagsService->searchTags($this->searchTerm, $this->language, true,$offset, $length);
            $this->nbResults = $searchResult->totalCount;

            return $searchResult->tags;
        } catch (UnauthorizedException $e) {
            $this->nbResults = 0;

            return [];
        }
    }
}
