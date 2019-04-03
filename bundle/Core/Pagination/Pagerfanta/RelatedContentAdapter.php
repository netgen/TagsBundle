<?php

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Pagerfanta adapter for content related to a tag.
 * Will return results as content objects.
 */
class RelatedContentAdapter implements AdapterInterface, TagAdapterInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected $tag;

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var bool
     */
    protected $returnContentInfo;

    /**
     * @var int
     */
    protected $nbResults;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param bool $returnContentInfo
     */
    public function __construct(TagsService $tagsService, $returnContentInfo = true)
    {
        $this->tagsService = $tagsService;
        $this->returnContentInfo = (bool) $returnContentInfo;
    }

    /**
     * Sets the tag to the adapter.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     */
    public function setTag(Tag $tag)
    {
        $this->tag = $tag;
    }

    /**
     * Returns the number of results.
     *
     * @return int The number of results
     */
    public function getNbResults()
    {
        if (!$this->tag instanceof Tag) {
            return 0;
        }

        if (!isset($this->nbResults)) {
            $this->nbResults = $this->tagsService->getRelatedContentCount($this->tag);
        }

        return $this->nbResults;
    }

    /**
     * Returns an slice of the results.
     *
     * @param int $offset The offset
     * @param int $length The length
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     */
    public function getSlice($offset, $length)
    {
        if (!$this->tag instanceof Tag) {
            return [];
        }

        $relatedContent = $this->tagsService->getRelatedContent(
            $this->tag,
            $offset,
            $length,
            $this->returnContentInfo
        );

        if (!isset($this->nbResults)) {
            $this->nbResults = $this->tagsService->getRelatedContentCount($this->tag);
        }

        return $relatedContent;
    }
}
