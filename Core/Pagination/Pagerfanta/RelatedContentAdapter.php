<?php

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\TagsService;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Pagerfanta adapter for content related to a tag.
 * Will return results as content objects.
 */
class RelatedContentAdapter implements AdapterInterface
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
     * Constructor
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct( Tag $tag, TagsService $tagsService )
    {
        $this->tag = $tag;
        $this->tagsService = $tagsService;
    }

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     */
    public function getNbResults()
    {
        if ( !isset( $this->nbResults ) )
        {
            $this->nbResults = $this->tagsService->getRelatedContentCount( $this->tag );
        }

        return $this->nbResults;
    }

    /**
     * Returns an slice of the results.
     *
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     */
    public function getSlice( $offset, $length )
    {
        $relatedContent = $this->tagsService->getRelatedContent( $this->tag, $offset, $length );

        if ( !isset( $this->nbResults ) )
        {
            $this->nbResults = $this->tagsService->getRelatedContentCount( $this->tag );
        }

        return $relatedContent;
    }
}
