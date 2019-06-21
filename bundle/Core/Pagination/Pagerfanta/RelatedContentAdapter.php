<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Pagerfanta adapter for content related to a tag.
 * Will return results as content objects.
 *
 * @final
 */
class RelatedContentAdapter implements AdapterInterface, TagAdapterInterface
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
     * @var bool
     */
    private $returnContentInfo;

    /**
     * @var int
     */
    private $nbResults;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    private $sortClauses = [];

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion[]
     */
    private $additionalCriteria = [];

    public function __construct(TagsService $tagsService, bool $returnContentInfo = true)
    {
        $this->tagsService = $tagsService;
        $this->returnContentInfo = $returnContentInfo;
    }

    public function setTag(Tag $tag): void
    {
        $this->tag = $tag;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     */
    public function setSortClauses(array $sortClauses): void
    {
        $this->sortClauses = $sortClauses;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion[] $additionalCriteria
     */
    public function setAdditionalCriteria(array $additionalCriteria = []): void
    {
        $this->additionalCriteria = $additionalCriteria;
    }

    public function getNbResults(): int
    {
        if (!$this->tag instanceof Tag) {
            return 0;
        }

        $this->nbResults = $this->nbResults ?? $this->tagsService->getRelatedContentCount($this->tag, $this->additionalCriteria);

        return $this->nbResults;
    }

    public function getSlice($offset, $length): iterable
    {
        if (!$this->tag instanceof Tag) {
            return [];
        }

        $relatedContent = $this->tagsService->getRelatedContent(
            $this->tag,
            $offset,
            $length,
            $this->returnContentInfo,
            $this->additionalCriteria,
            $this->sortClauses
        );

        $this->nbResults = $this->nbResults ?? $this->tagsService->getRelatedContentCount($this->tag, $this->additionalCriteria);

        return $relatedContent;
    }
}
