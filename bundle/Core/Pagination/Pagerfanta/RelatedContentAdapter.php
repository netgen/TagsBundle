<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Pagerfanta adapter for content related to a tag.
 * Will return results as content objects.
 */
final class RelatedContentAdapter implements AdapterInterface, TagAdapterInterface
{
    private Tag $tag;

    private TagsService $tagsService;

    private ConfigResolverInterface $configResolver;

    private int $nbResults;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause[]
     */
    private array $sortClauses = [];

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion[]
     */
    private array $additionalCriteria = [];

    public function __construct(TagsService $tagsService, ConfigResolverInterface $configResolver)
    {
        $this->tagsService = $tagsService;
        $this->configResolver = $configResolver;
    }

    public function setTag(Tag $tag): void
    {
        $this->tag = $tag;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause[] $sortClauses
     */
    public function setSortClauses(array $sortClauses): void
    {
        $this->sortClauses = $sortClauses;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion[] $additionalCriteria
     */
    public function setAdditionalCriteria(array $additionalCriteria = []): void
    {
        $this->additionalCriteria = $additionalCriteria;
    }

    public function getNbResults(): int
    {
        if (!isset($this->tag)) {
            return 0;
        }

        $this->nbResults = $this->nbResults ?? $this->tagsService->getRelatedContentCount($this->tag, $this->additionalCriteria);

        return $this->nbResults;
    }

    public function getSlice($offset, $length): iterable
    {
        if (!isset($this->tag)) {
            return [];
        }

        $relatedContent = $this->tagsService->getRelatedContent(
            $this->tag,
            $offset,
            $length,
            $this->configResolver->getParameter('tag_view.related_content_list.return_content_info', 'netgen_tags'),
            $this->additionalCriteria,
            $this->sortClauses
        );

        $this->nbResults = $this->nbResults ?? $this->tagsService->getRelatedContentCount($this->tag, $this->additionalCriteria);

        return $relatedContent;
    }
}
