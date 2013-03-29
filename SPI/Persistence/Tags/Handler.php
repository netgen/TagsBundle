<?php

namespace EzSystems\TagsBundle\SPI\Persistence\Tags;

use EzSystems\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use EzSystems\TagsBundle\SPI\Persistence\Tags\UpdateStruct;

interface Handler
{
    /**
     * Loads a tag object from its $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function load( $tagId );

    /**
     * Loads a tag object from its $remoteId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param string $remoteId
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function loadByRemoteId( $remoteId );

    /**
     * Loads children of a tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all children starting at $offset are returned
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function loadChildren( $tagId, $offset = 0, $limit = -1 );

    /**
     * Returns the number of children of a tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     *
     * @return int
     */
    public function getChildCount( $tagId );

    /**
     * Creates the new tag
     *
     * @param \EzSystems\TagsBundle\SPI\Persistence\Tags\CreateStruct $createStruct
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag The newly created tag
     */
    public function create( CreateStruct $createStruct );

    /**
     * Updates tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param \EzSystems\TagsBundle\SPI\Persistence\Tags\UpdateStruct $updateStruct
     * @param mixed $tagId
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag The updated tag
     */
    public function update( UpdateStruct $updateStruct, $tagId );

    /**
     * Creates a synonym for tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     * @param string $keyword
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag The created synonym
     */
    public function addSynonym( $tagId, $keyword );

    /**
     * Converts tag identified by $tagId to a synonym of tag identified by $mainTagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $tagId or $mainTagId are invalid
     *
     * @param mixed $tagId
     * @param mixed $mainTagId
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag The converted synonym
     */
    public function convertToSynonym( $tagId, $mainTagId );

    /**
     * Merges the tag identified by $tagId into the tag identified $targetTagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $tagId or $targetTagId are invalid
     *
     * @param mixed $tagId
     * @param mixed $targetTagId
     */
    public function merge( $tagId, $targetTagId );

    /**
     * Swaps the locations of tags identified by $tagId1 and $tagId2
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $tagId1 or $tagId2 are invalid
     *
     * @param mixed $tagId1
     * @param mixed $tagId2
     */
    public function swap( $tagId1, $tagId2 );

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
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag The newly created tag of the copied subtree
     */
    public function copySubtree( $sourceId, $destinationParentId );

    /**
     * Moves a tag identified by $sourceId into new parent identified by $destinationParentId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $sourceId or $destinationParentId are invalid
     *
     * @param mixed $sourceId
     * @param mixed $destinationParentId
     */
    public function moveSubtree( $sourceId, $destinationParentId );

    /**
     * Deletes a tag identified by $tagId and all its descendants and synonyms
     *
     * If $tagId is a synonym, only the synonym is deleted
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     */
    public function delete( $tagId );
}
