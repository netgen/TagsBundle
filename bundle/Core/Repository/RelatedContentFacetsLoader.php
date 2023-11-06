<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Repository;

use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Exception\FacetingNotSupportedException;

use function count;

final class RelatedContentFacetsLoader
{
    public function __construct(private TagsService $tagsService, private SearchService $searchService) {}

    /**
     * Returns facets for given $facetBuilders,
     * for content tagged with $tag.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     * @throws \Netgen\TagsBundle\Exception\FacetingNotSupportedException
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Search\Facet[]
     */
    public function getRelatedContentFacets(Tag $tag, array $facetBuilders = []): array
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
                ],
            ),
        );

        return $searchResult->facets;
    }
}
