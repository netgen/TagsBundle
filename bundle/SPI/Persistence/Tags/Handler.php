<?php

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
     * @param int $tagId
     * @param string[] $translations
     * @param bool $useAlwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function load($tagId, array $translations = null, $useAlwaysAvailable = true);

    /**
     * Loads a tag object from array of $tagIds.
     *
     * Tags missing (NotFound) will be filtered out from the returned array.
     *
     * @param array $tagIds
     * @param array|null $translations A language filter for keywords. If not given all languages are returned
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[] Key of array is the corresponding tag id
     */
    public function loadList(array $tagIds, array $translations = null, $useAlwaysAvailable = true);

    /**
     * Loads a tag info object from its $tagId.
     *
     * @param int $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo
     */
    public function loadTagInfo($tagId);

    /**
     * Loads a tag object from its $remoteId.
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @param string $remoteId
     * @param string[] $translations
     * @param bool $useAlwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function loadByRemoteId($remoteId, array $translations = null, $useAlwaysAvailable = true);

    /**
     * Loads a tag info object from its remote ID.
     *
     * @param string $remoteId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo
     */
    public function loadTagInfoByRemoteId($remoteId);

    /**
     * Loads tag by specified keyword and parent ID.
     *
     * @param string $keyword The keyword to fetch tag for
     * @param int $parentTagId The parent ID to fetch tag for
     * @param string[] $translations The languages to load
     * @param bool $useAlwaysAvailable Check for main language if true (default) and if tag is always available
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTagByKeywordAndParentId($keyword, $parentTagId, array $translations = null, $useAlwaysAvailable = true);

    /**
     * Loads children of a tag identified by $tagId.
     *
     * @param int $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all children starting at $offset are returned
     * @param string[] $translations
     * @param bool $useAlwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function loadChildren($tagId, $offset = 0, $limit = -1, array $translations = null, $useAlwaysAvailable = true);

    /**
     * Returns the number of children of a tag identified by $tagId.
     *
     * @param int $tagId
     * @param string[] $translations
     * @param bool $useAlwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return int
     */
    public function getChildrenCount($tagId, array $translations = null, $useAlwaysAvailable = true);

    /**
     * Loads tags with specified $keyword.
     *
     * @param string $keyword
     * @param string $translation
     * @param bool $useAlwaysAvailable
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all tags starting at $offset are returned
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function loadTagsByKeyword($keyword, $translation, $useAlwaysAvailable = true, $offset = 0, $limit = -1);

    /**
     * Returns the number of tags with specified $keyword.
     *
     * @param string $keyword
     * @param string $translation
     * @param bool $useAlwaysAvailable
     *
     * @return int
     */
    public function getTagsByKeywordCount($keyword, $translation, $useAlwaysAvailable = true);

    /**
     * Searches for tags.
     *
     * @param string $searchString
     * @param string $translation
     * @param bool $useAlwaysAvailable
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all tags starting at $offset are returned
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\SearchResult
     */
    public function searchTags($searchString, $translation, $useAlwaysAvailable = true, $offset = 0, $limit = -1);

    /**
     * Loads the synonyms of a tag identified by $tagId.
     *
     * @param int $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all synonyms starting at $offset are returned
     * @param string[] $translations
     * @param bool $useAlwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function loadSynonyms($tagId, $offset = 0, $limit = -1, array $translations = null, $useAlwaysAvailable = true);

    /**
     * Returns the number of synonyms of a tag identified by $tagId.
     *
     * @param int $tagId
     * @param string[] $translations
     * @param bool $useAlwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return int
     */
    public function getSynonymCount($tagId, array $translations = null, $useAlwaysAvailable = true);

    /**
     * Creates the new tag.
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct $createStruct
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The newly created tag
     */
    public function create(CreateStruct $createStruct);

    /**
     * Updates tag identified by $tagId.
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct $updateStruct
     * @param int $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The updated tag
     */
    public function update(UpdateStruct $updateStruct, $tagId);

    /**
     * Creates a synonym.
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct $createStruct
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The created synonym
     */
    public function addSynonym(SynonymCreateStruct $createStruct);

    /**
     * Converts tag identified by $tagId to a synonym of tag identified by $mainTagId.
     *
     * @param int $tagId
     * @param int $mainTagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $tagId or $mainTagId are invalid
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The converted synonym
     */
    public function convertToSynonym($tagId, $mainTagId);

    /**
     * Merges the tag identified by $tagId into the tag identified by $targetTagId.
     *
     * @param int $tagId
     * @param int $targetTagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $tagId or $targetTagId are invalid
     */
    public function merge($tagId, $targetTagId);

    /**
     * Copies tag object identified by $sourceId into destination identified by $destinationParentId.
     *
     * Also performs a copy of all child locations of $sourceId tag
     *
     * @param int $sourceId The subtree denoted by the tag to copy
     * @param int $destinationParentId The target parent tag for the copy operation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $sourceId or $destinationParentId are invalid
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The newly created tag of the copied subtree
     */
    public function copySubtree($sourceId, $destinationParentId);

    /**
     * Moves a tag identified by $sourceId into new parent identified by $destinationParentId.
     *
     * @param int $sourceId
     * @param int $destinationParentId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $sourceId or $destinationParentId are invalid
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The updated root tag of the moved subtree
     */
    public function moveSubtree($sourceId, $destinationParentId);

    /**
     * Deletes tag identified by $tagId, including its synonyms and all tags under it.
     *
     * @param int $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * If $tagId is a synonym, only the synonym is deleted
     */
    public function deleteTag($tagId);
}
