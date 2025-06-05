<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\SPI\Persistence\Tags;

interface Handler
{
    /**
     * Loads a tag object from its $tagId.
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function load(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): Tag;

    /**
     * Loads a tag object from array of $tagIds.
     *
     * Tags missing (NotFound) will be filtered out from the returned array.
     */
    public function loadList(array $tagIds, ?array $translations = null, bool $useAlwaysAvailable = true): array;

    /**
     * Loads a tag info object from its $tagId.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function loadTagInfo(int $tagId): TagInfo;

    /**
     * Loads a tag object from its $remoteId.
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function loadByRemoteId(string $remoteId, ?array $translations = null, bool $useAlwaysAvailable = true): Tag;

    /**
     * Loads a tag info object from its remote ID.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function loadTagInfoByRemoteId(string $remoteId): TagInfo;

    /**
     * Loads tag by specified keyword and parent ID.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function loadTagByKeywordAndParentId(string $keyword, int $parentTagId, ?array $translations = null, bool $useAlwaysAvailable = true): Tag;

    /**
     * Loads children of a tag identified by $tagId.
     *
     * If $limit = -1 all children starting at $offset are returned.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function loadChildren(int $tagId, int $offset = 0, int $limit = -1, ?array $translations = null, bool $useAlwaysAvailable = true): array;

    /**
     * Returns the number of children of a tag identified by $tagId.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function getChildrenCount(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): int;

    /**
     * Loads tags with specified $keyword.
     *
     * If $limit = -1 all tags starting at $offset are returned.
     */
    public function loadTagsByKeyword(string $keyword, string $translation, bool $useAlwaysAvailable = true, int $offset = 0, int $limit = -1): array;

    /**
     * Returns the number of tags with specified $keyword.
     */
    public function getTagsByKeywordCount(string $keyword, string $translation, bool $useAlwaysAvailable = true): int;

    /**
     * Searches for tags.
     *
     * If $limit = -1 all tags starting at $offset are returned.
     */
    public function searchTags(string $searchString, string $translation, bool $useAlwaysAvailable = true, int $offset = 0, int $limit = -1): SearchResult;

    /**
     * Loads the synonyms of a tag identified by $tagId.
     *
     * If $limit = -1 all synonyms starting at $offset are returned.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function loadSynonyms(int $tagId, int $offset = 0, int $limit = -1, ?array $translations = null, bool $useAlwaysAvailable = true): array;

    /**
     * Returns the number of synonyms of a tag identified by $tagId.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function getSynonymCount(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): int;

    /**
     * Creates the new tag.
     */
    public function create(CreateStruct $createStruct): Tag;

    /**
     * Updates tag identified by $tagId.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function update(UpdateStruct $updateStruct, int $tagId): Tag;

    /**
     * Creates a synonym.
     */
    public function addSynonym(SynonymCreateStruct $createStruct): Tag;

    /**
     * Converts tag identified by $tagId to a synonym of tag identified by $mainTagId.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If $tagId or $mainTagId are invalid
     */
    public function convertToSynonym(int $tagId, int $mainTagId): Tag;

    /**
     * Merges the tag identified by $tagId into the tag identified by $targetTagId.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If $tagId or $targetTagId are invalid
     */
    public function merge(int $tagId, int $targetTagId): void;

    /**
     * Copies tag object identified by $sourceId into destination identified by $destinationParentId.
     *
     * Also performs a copy of all child locations of $sourceId tag.
     *
     * Returns the newly created tag of the copied subtree.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If $sourceId or $destinationParentId are invalid
     */
    public function copySubtree(int $sourceId, int $destinationParentId): Tag;

    /**
     * Moves a tag identified by $sourceId into new parent identified by $destinationParentId.
     *
     * Returns the updated root tag of the moved subtree.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If $sourceId or $destinationParentId are invalid
     */
    public function moveSubtree(int $sourceId, int $destinationParentId): Tag;

    /**
     * Deletes tag identified by $tagId, including its synonyms and all tags under it.
     *
     * If $tagId is a synonym, only the synonym is deleted.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function deleteTag(int $tagId): void;

    /**
     * Hides tag identified by $tagId.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function hideTag(int $tagId): void;

    /**
     * Unhides tag identified by $tagId.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function unhideTag(int $tagId): void;
}
