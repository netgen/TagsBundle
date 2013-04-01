<?php

namespace EzSystems\TagsBundle\Core\Persistence\Legacy\Tags;

use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use EzSystems\TagsBundle\SPI\Persistence\Tags\Handler as BaseTagsHandler;
use EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;
use EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Mapper;
use EzSystems\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use EzSystems\TagsBundle\SPI\Persistence\Tags\Tag;
use EzSystems\TagsBundle\SPI\Persistence\Tags\UpdateStruct;

class Handler implements BaseTagsHandler
{
    /**
     * @var \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway
     */
    protected $gateway;

    /**
     * @var \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Mapper
     */
    protected $mapper;

    /**
     * @param \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway $gateway
     * @param \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Mapper $mapper
     */
    public function __construct( Gateway $gateway, Mapper $mapper )
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
    }

    /**
     * Loads a tag object from its $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function load( $tagId )
    {
        $data = $this->gateway->getBasicTagData( $tagId );
        return $this->mapper->createTagFromRow( $data );
    }

    /**
     * Loads a tag object from its $remoteId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param string $remoteId
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function loadByRemoteId( $remoteId )
    {
        $data = $this->gateway->getBasicTagDataByRemoteId( $remoteId );
        return $this->mapper->createTagFromRow( $data );
    }

    /**
     * Loads children of a tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = 0 all children starting at $offset are returned
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function loadChildren( $tagId, $offset = 0, $limit = 0 )
    {
        $tags = $this->gateway->getChildren( $tagId, $offset, $limit );
        return $this->mapper->createTagsFromRows( $tags );
    }

    /**
     * Returns the number of children of a tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     *
     * @return int
     */
    public function getChildrenCount( $tagId )
    {
        return $this->gateway->getChildrenCount( $tagId );
    }

    /**
     * Creates the new tag
     *
     * @param \EzSystems\TagsBundle\SPI\Persistence\Tags\CreateStruct $createStruct
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag The newly created tag
     */
    public function create( CreateStruct $createStruct )
    {
        $parentTagData = $this->gateway->getBasicTagData( $createStruct->parentTagId );
        $newTag = $this->gateway->create( $createStruct, $parentTagData );
        $this->updateSubtreeModificationTime( $newTag, $newTag->modificationDate );

        return $newTag;
    }

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
    public function update( UpdateStruct $updateStruct, $tagId )
    {
        $this->gateway->update( $updateStruct, $tagId );

        $this->updateSubtreeModificationTime( $this->load( $tagId ) );
        return $this->load( $tagId );
    }

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
    public function addSynonym( $tagId, $keyword )
    {
        $tagData = $this->gateway->getBasicTagData( $tagId );
        $newSynonym = $this->gateway->createSynonym( $keyword, $tagData );

        $tag = $this->mapper->createTagFromRow( $tagData );
        $this->updateSubtreeModificationTime( $tag, $newSynonym->modificationDate );

        return $newSynonym;
    }

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
    public function convertToSynonym( $tagId, $mainTagId )
    {
    }

    /**
     * Merges the tag identified by $tagId into the tag identified $targetTagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $tagId or $targetTagId are invalid
     *
     * @param mixed $tagId
     * @param mixed $targetTagId
     */
    public function merge( $tagId, $targetTagId )
    {
    }

    /**
     * Swaps the locations of tags identified by $tagId1 and $tagId2
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $tagId1 or $tagId2 are invalid
     *
     * @param mixed $tagId1
     * @param mixed $tagId2
     */
    public function swap( $tagId1, $tagId2 )
    {
    }

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
    public function copySubtree( $sourceId, $destinationParentId )
    {
        $sourceData = $this->gateway->getBasicTagData( $sourceId );
        $destinationParentData = $this->gateway->getBasicTagData( $destinationParentId );

        return $this->recursiveCopySubtree( $sourceData, $destinationParentData );
    }

    /**
     * Copies tag object identified by $sourceData into destination identified by $destinationParentData
     *
     * Also performs a copy of all child locations of $sourceData tag

     * @param mixed $sourceData The subtree denoted by the tag to copy
     * @param mixed $destinationParentData The target parent tag for the copy operation
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag The newly created tag of the copied subtree
     */
    protected function recursiveCopySubtree( array $sourceData, array $destinationParentData )
    {
        // First copy the root node
        $createStruct = $this->mapper->getTagCreateStruct( $sourceData );
        $createStruct->parentTagId = $destinationParentData["id"];
        $createdTag = $this->gateway->create( $createStruct, $destinationParentData );

        // TODO: Copy root node synonyms

        // Then copy the children
        $children = $this->gateway->getChildren( $sourceData["id"] );
        foreach ( $children as $child )
        {
            $this->recursiveCopySubtree(
                $child,
                $this->gateway->getBasicTagData( $createdTag->id )
            );
        }

        return $createdTag;
    }

    /**
     * Moves a tag identified by $sourceId into new parent identified by $destinationParentId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $sourceId or $destinationParentId are invalid
     *
     * @param mixed $sourceId
     * @param mixed $destinationParentId
     */
    public function moveSubtree( $sourceId, $destinationParentId )
    {
        $sourceTagData = $this->gateway->getBasicTagData( $sourceId );
        $destinationParentTagData = $this->gateway->getBasicTagData( $destinationParentId );

        $this->gateway->moveSubtree( $sourceTagData, $destinationParentTagData );

        $timestamp = time();
        if ( $sourceTagData["parent_id"] > 0 )
        {
            $this->updateSubtreeModificationTime( $this->load( $sourceTagData["parent_id"] ), $timestamp );
        }

        $this->updateSubtreeModificationTime( $this->load( $destinationParentTagData["id"] ), $timestamp );
    }

    /**
     * Deletes tag identified by $tagId, including its synonyms and all tags under it
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * If $tagId is a synonym, only the synonym is deleted
     *
     * @param mixed $tagId
     */
    public function deleteTag( $tagId )
    {
        $tag = $this->load( $tagId );

        $this->gateway->deleteTag( $tag->id );
        $this->updateSubtreeModificationTime( $tag );
    }

    /**
     * Updated subtree modification time for tag and all its parents
     *
     * If tag is a synonym, subtree modification time of its main tag is updated
     *
     * @param \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag $tag
     * @param int $timestamp
     */
    protected function updateSubtreeModificationTime( Tag $tag, $timestamp = null )
    {
        $timestamp = $timestamp ?: time();
        $this->gateway->updateSubtreeModificationTime( $tag->pathString, $timestamp );

        if ( $tag->mainTagId > 0 )
        {
            $this->gateway->updateSubtreeModificationTime( (string)$tag->mainTagId, $timestamp );
        }
    }
}
