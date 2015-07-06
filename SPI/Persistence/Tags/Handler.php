<?php

namespace Netgen\TagsBundle\SPI\Persistence\Tags;

interface Handler
{
    /**
     * Loads a tag object from its $tagId
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     * @param string[] $translations
     * @param boolean $useAlwaysAvailable
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function load( $tagId, array $translations = null, $useAlwaysAvailable = true );

    /**
     * Loads a tag info object from its $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo
     */
    public function loadTagInfo( $tagId );

    /**
     * Loads a tag object from its $remoteId
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param string $remoteId
     * @param string[] $translations
     * @param boolean $useAlwaysAvailable
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function loadByRemoteId( $remoteId, array $translations = null, $useAlwaysAvailable = true );

    /**
     * Loads a tag info object from its remote ID
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param string $remoteId
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo
     */
    public function loadTagInfoByRemoteId( $remoteId );

    /**
     * Loads a tag object from its URL
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param string $url
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function loadByUrl( $url );

    /**
     * Loads children of a tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all children starting at $offset are returned
     * @param string[] $translations
     * @param boolean $useAlwaysAvailable
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function loadChildren( $tagId, $offset = 0, $limit = -1, array $translations = null, $useAlwaysAvailable = true );

    /**
     * Returns the number of children of a tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     * @param string[] $translations
     * @param boolean $useAlwaysAvailable
     *
     * @return int
     */
    public function getChildrenCount( $tagId, array $translations = null, $useAlwaysAvailable = true );

    /**
     * Loads tags with specified $keyword
     *
     * @param string $keyword
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all tags starting at $offset are returned
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function loadTagsByKeyword( $keyword, $offset = 0, $limit = -1 );

    /**
     * Returns the number of tags with specified $keyword
     *
     * @param string $keyword
     *
     * @return int
     */
    public function getTagsByKeywordCount( $keyword );

    /**
     * Loads the synonyms of a tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all synonyms starting at $offset are returned
     * @param string[] $translations
     * @param boolean $useAlwaysAvailable
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function loadSynonyms( $tagId, $offset = 0, $limit = -1, array $translations = null, $useAlwaysAvailable = true );

    /**
     * Returns the number of synonyms of a tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     * @param string[] $translations
     * @param boolean $useAlwaysAvailable
     *
     * @return int
     */
    public function getSynonymCount( $tagId, array $translations = null, $useAlwaysAvailable = true );

    /**
     * Loads content IDs related to tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of content IDs returned. If $limit = -1 all content IDs starting at $offset are returned
     *
     * @return array
     */
    public function loadRelatedContentIds( $tagId, $offset = 0, $limit = -1 );

    /**
     * Returns the number of content objects related to tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     *
     * @return int
     */
    public function getRelatedContentCount( $tagId );

    /**
     * Creates the new tag
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct $createStruct
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The newly created tag
     */
    public function create( CreateStruct $createStruct );

    /**
     * Updates tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct $updateStruct
     * @param mixed $tagId
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The updated tag
     */
    public function update( UpdateStruct $updateStruct, $tagId );

    /**
     * Creates a synonym
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct $createStruct
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The created synonym
     */
    public function addSynonym( $createStruct );

    /**
     * Converts tag identified by $tagId to a synonym of tag identified by $mainTagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $tagId or $mainTagId are invalid
     *
     * @param mixed $tagId
     * @param mixed $mainTagId
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The converted synonym
     */
    public function convertToSynonym( $tagId, $mainTagId );

    /**
     * Merges the tag identified by $tagId into the tag identified by $targetTagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $tagId or $targetTagId are invalid
     *
     * @param mixed $tagId
     * @param mixed $targetTagId
     */
    public function merge( $tagId, $targetTagId );

    /**
     * Copies tag object identified by $sourceId into destination identified by $destinationParentId
     *
     * Also performs a copy of all child locations of $sourceId tag
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $sourceId or $destinationParentId are invalid
     *
     * @param mixed $sourceId The subtree denoted by the tag to copy
     * @param mixed $destinationParentId The target parent tag for the copy operation
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The newly created tag of the copied subtree
     */
    public function copySubtree( $sourceId, $destinationParentId );

    /**
     * Moves a tag identified by $sourceId into new parent identified by $destinationParentId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $sourceId or $destinationParentId are invalid
     *
     * @param mixed $sourceId
     * @param mixed $destinationParentId
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The updated root tag of the moved subtree
     */
    public function moveSubtree( $sourceId, $destinationParentId );

    /**
     * Deletes tag identified by $tagId, including its synonyms and all tags under it
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * If $tagId is a synonym, only the synonym is deleted
     *
     * @param mixed $tagId
     */
    public function deleteTag( $tagId );
}
