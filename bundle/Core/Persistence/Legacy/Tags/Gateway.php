<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Tags;

use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;

abstract class Gateway
{
    /**
     * Returns an array with basic tag data.
     */
    abstract public function getBasicTagData(int $tagId): array;

    /**
     * Returns an array with basic tag data by remote ID.
     */
    abstract public function getBasicTagDataByRemoteId(string $remoteId): array;

    /**
     * Returns an array with full tag data.
     */
    abstract public function getFullTagData(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): array;

    /**
     * Returns an array with basic tag data for the tag with $remoteId.
     */
    abstract public function getFullTagDataByRemoteId(string $remoteId, ?array $translations = null, bool $useAlwaysAvailable = true): array;

    /**
     * Returns an array with full tag data for the tag with $parentId parent ID and $keyword keyword.
     */
    abstract public function getFullTagDataByKeywordAndParentId(string $keyword, int $parentId, ?array $translations = null, bool $useAlwaysAvailable = true): array;

    /**
     * Returns data for the first level children of the tag identified by given $tagId.
     *
     * If $limit = -1 all children starting at $offset are returned.
     */
    abstract public function getChildren(int $tagId, int $offset = 0, int $limit = -1, ?array $translations = null, bool $useAlwaysAvailable = true): array;

    /**
     * Returns how many tags exist below tag identified by $tagId.
     */
    abstract public function getChildrenCount(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): int;

    /**
     * Returns data for tags identified by given $keyword.
     *
     * If $limit = -1 all tags starting at $offset are returned.
     */
    abstract public function getTagsByKeyword(string $keyword, string $translation, bool $useAlwaysAvailable = true, bool $exactMatch = true, int $offset = 0, int $limit = -1): array;

    /**
     * Returns how many tags exist with $keyword.
     */
    abstract public function getTagsByKeywordCount(string $keyword, string $translation, bool $useAlwaysAvailable = true, bool $exactMatch = true): int;

    /**
     * Returns data for synonyms of the tag identified by given $tagId.
     *
     * If $limit = -1 all synonyms starting at $offset are returned.
     */
    abstract public function getSynonyms(int $tagId, int $offset = 0, int $limit = -1, ?array $translations = null, bool $useAlwaysAvailable = true): array;

    /**
     * Returns how many synonyms exist for a tag identified by $tagId.
     */
    abstract public function getSynonymCount(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): int;

    /**
     * Moves the synonym identified by $synonymId to tag identified by $mainTagData.
     */
    abstract public function moveSynonym(int $synonymId, array $mainTagData): void;

    /**
     * Creates a new tag using the given $createStruct below $parentTag.
     */
    abstract public function create(CreateStruct $createStruct, ?array $parentTag = null): int;

    /**
     * Updates an existing tag.
     */
    abstract public function update(UpdateStruct $updateStruct, int $tagId): void;

    /**
     * Creates a new synonym using the given $keyword for tag $tag.
     */
    abstract public function createSynonym(SynonymCreateStruct $createStruct, array $tag): int;

    /**
     * Converts tag identified by $tagId to a synonym of tag identified by $mainTagData.
     */
    abstract public function convertToSynonym(int $tagId, array $mainTagData): void;

    /**
     * Transfers all tag attribute links from tag identified by $tagId into the tag identified by $targetTagId.
     */
    abstract public function transferTagAttributeLinks(int $tagId, int $targetTagId): void;

    /**
     * Moves a tag identified by $sourceTagData into new parent identified by $destinationParentTagData.
     */
    abstract public function moveSubtree(array $sourceTagData, ?array $destinationParentTagData = null): void;

    /**
     * Deletes tag identified by $tagId, including its synonyms and all tags under it.
     *
     * If $tagId is a synonym, only the synonym is deleted.
     */
    abstract public function deleteTag(int $tagId): void;
}
