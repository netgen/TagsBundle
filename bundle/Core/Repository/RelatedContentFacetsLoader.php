<?php

namespace Netgen\TagsBundle\Core\Repository;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Exception\FacetingNotSupportedException;

class RelatedContentFacetsLoader
{
    /**
     * @var \Netgen\TagsBundle\Core\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    private $searchService;

    /**
     * RelatedContentFacetsLoader constructor.
     *
     * @param \Netgen\TagsBundle\Core\Repository\TagsService $tagsService
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     */
    public function __construct(TagsService $tagsService, SearchService $searchService)
    {
        $this->tagsService = $tagsService;
        $this->searchService = $searchService;
    }

    /**
     * Returns facets for given $facetBuilders,
     * for content tagged with $tag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     * @throws \Netgen\TagsBundle\Exception\FacetingNotSupportedException
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\Facet[]
     */
    public function getRelatedContentFacets(Tag $tag, array $facetBuilders = [])
    {
        if ($this->tagsService->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        if (!$this->searchService->supports(SearchService::CAPABILITY_FACETS)) {
            throw new FacetingNotSupportedException('Faceting for related content is not supported');
        }

        if (count($facetBuilders) === 0) {
            return [];
        }

        $searchResult = $this->searchService->findContentInfo(
            new Query(
                [
                    'limit' => 0,
                    'filter' => new TagId($tag->id),
                    'facetBuilders' => $facetBuilders,
                ]
            )
        );

        return $searchResult->facets;
    }
}
