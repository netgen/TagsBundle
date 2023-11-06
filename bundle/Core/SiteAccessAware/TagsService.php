<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\SiteAccessAware;

use Ibexa\Contracts\Core\Repository\LanguageResolver;
use Netgen\TagsBundle\API\Repository\TagsService as TagsServiceInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\SearchResult;
use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagList;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;

final class TagsService implements TagsServiceInterface
{
    public function __construct(private TagsServiceInterface $innerService, private LanguageResolver $languageResolver) {}

    public function loadTag(int $tagId, ?array $languages = null, bool $useAlwaysAvailable = true): Tag
    {
        return $this->innerService->loadTag(
            $tagId,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable),
        );
    }

    public function loadTagList(array $tagIds, ?array $languages = null, bool $useAlwaysAvailable = true): TagList
    {
        return $this->innerService->loadTagList(
            $tagIds,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable),
        );
    }

    public function loadTagByRemoteId(string $remoteId, ?array $languages = null, bool $useAlwaysAvailable = true): Tag
    {
        return $this->innerService->loadTagByRemoteId(
            $remoteId,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable),
        );
    }

    public function loadTagByUrl(string $url, array $languages): Tag
    {
        return $this->innerService->loadTagByUrl(
            $url,
            $this->languageResolver->getPrioritizedLanguages($languages),
        );
    }

    public function loadTagChildren(?Tag $tag = null, int $offset = 0, int $limit = -1, ?array $languages = null, bool $useAlwaysAvailable = true): TagList
    {
        return $this->innerService->loadTagChildren(
            $tag,
            $offset,
            $limit,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable),
        );
    }

    public function getTagChildrenCount(?Tag $tag = null, ?array $languages = null, bool $useAlwaysAvailable = true): int
    {
        return $this->innerService->getTagChildrenCount(
            $tag,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable),
        );
    }

    public function loadTagsByKeyword(string $keyword, string $language, bool $useAlwaysAvailable = true, int $offset = 0, int $limit = -1): TagList
    {
        return $this->innerService->loadTagsByKeyword(
            $keyword,
            $language,
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable),
            $offset,
            $limit,
        );
    }

    public function getTagsByKeywordCount(string $keyword, string $language, bool $useAlwaysAvailable = true): int
    {
        return $this->innerService->getTagsByKeywordCount(
            $keyword,
            $language,
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable),
        );
    }

    public function searchTags(string $searchString, string $language, bool $useAlwaysAvailable = true, int $offset = 0, int $limit = -1): SearchResult
    {
        return $this->innerService->searchTags(
            $searchString,
            $language,
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable),
            $offset,
            $limit,
        );
    }

    public function loadTagSynonyms(Tag $tag, int $offset = 0, int $limit = -1, ?array $languages = null, bool $useAlwaysAvailable = true): TagList
    {
        return $this->innerService->loadTagSynonyms(
            $tag,
            $offset,
            $limit,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable),
        );
    }

    public function getTagSynonymCount(Tag $tag, ?array $languages = null, bool $useAlwaysAvailable = true): int
    {
        return $this->innerService->getTagSynonymCount(
            $tag,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable),
        );
    }

    public function getRelatedContent(Tag $tag, int $offset = 0, int $limit = -1, bool $returnContentInfo = true, array $additionalCriteria = [], array $sortClauses = []): array
    {
        return $this->innerService->getRelatedContent($tag, $offset, $limit, $returnContentInfo, $additionalCriteria, $sortClauses);
    }

    public function getRelatedContentCount(Tag $tag, array $additionalCriteria = []): int
    {
        return $this->innerService->getRelatedContentCount($tag, $additionalCriteria);
    }

    public function createTag(TagCreateStruct $tagCreateStruct): Tag
    {
        return $this->innerService->createTag($tagCreateStruct);
    }

    public function updateTag(Tag $tag, TagUpdateStruct $tagUpdateStruct): Tag
    {
        return $this->innerService->updateTag($tag, $tagUpdateStruct);
    }

    public function addSynonym(SynonymCreateStruct $synonymCreateStruct): Tag
    {
        return $this->innerService->addSynonym($synonymCreateStruct);
    }

    public function convertToSynonym(Tag $tag, Tag $mainTag): Tag
    {
        return $this->innerService->convertToSynonym($tag, $mainTag);
    }

    public function mergeTags(Tag $tag, Tag $targetTag): void
    {
        $this->innerService->mergeTags($tag, $targetTag);
    }

    public function copySubtree(Tag $tag, ?Tag $targetParentTag = null): Tag
    {
        return $this->innerService->copySubtree($tag, $targetParentTag);
    }

    public function moveSubtree(Tag $tag, ?Tag $targetParentTag = null): Tag
    {
        return $this->innerService->moveSubtree($tag, $targetParentTag);
    }

    public function deleteTag(Tag $tag): void
    {
        $this->innerService->deleteTag($tag);
    }

    public function newTagCreateStruct(int $parentTagId, string $mainLanguageCode): TagCreateStruct
    {
        return $this->innerService->newTagCreateStruct($parentTagId, $mainLanguageCode);
    }

    public function newSynonymCreateStruct(int $mainTagId, string $mainLanguageCode): SynonymCreateStruct
    {
        return $this->innerService->newSynonymCreateStruct($mainTagId, $mainLanguageCode);
    }

    public function newTagUpdateStruct(): TagUpdateStruct
    {
        return $this->innerService->newTagUpdateStruct();
    }

    public function sudo(callable $callback, ?TagsServiceInterface $outerTagsService = null): mixed
    {
        return $this->innerService->sudo($callback, $outerTagsService ?? $this);
    }
}
