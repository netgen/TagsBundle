<?php

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\TagsService;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Pagerfanta adapter for children tags of a tag.
 * Will return results as content objects.
 */
class ChildrenTagsAdapter implements AdapterInterface, TagAdapterInterface
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
     * @var int
     */
    protected $nbResults;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
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
            $this->nbResults = $this->tagsService->getTagChildrenCount($this->tag);
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
            return array();
        }

        $childrenTags = $this->tagsService->loadTagChildren($this->tag, $offset, $length);

        if (!isset($this->nbResults)) {
            $this->nbResults = $this->tagsService->getTagChildrenCount($this->tag);
        }

        return $childrenTags;
    }
}
