<?php

namespace EzSystems\TagsBundle\Core\Persistence\Legacy\Tags;

use EzSystems\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use EzSystems\TagsBundle\SPI\Persistence\Tags\UpdateStruct;

abstract class Gateway
{
    /**
     * Returns an array with basic tag data
     *
     * @param mixed $tagId
     *
     * @return array
     */
    abstract public function getBasicTagData( $tagId );

    /**
     * Returns an array with basic tag data for the tag with $remoteId
     *
     * @param string $remoteId
     *
     * @return array
     */
    abstract public function getBasicTagDataByRemoteId( $remoteId );

    /**
     * Returns data for the first level children of the tag identified by given $tagId
     *
     * @param mixed $tagId
     * @param integer $offset The start offset for paging
     * @param integer $limit The number of tags returned. If $limit = 0 all children starting at $offset are returned
     *
     * @return array
     */
    abstract public function getChildren( $tagId, $offset = 0, $limit = 0 );

    /**
     * Returns how many tags exist below tag identified by $tagId
     *
     * @param int $tagId
     *
     * @return int
     */
    abstract public function getChildrenCount( $tagId );

    /**
     * Returns data for synonyms of the tag identified by given $tagId
     *
     * @param mixed $tagId
     * @param integer $offset The start offset for paging
     * @param integer $limit The number of tags returned. If $limit = 0 all synonyms starting at $offset are returned
     *
     * @return array
     */
    abstract public function getSynonyms( $tagId, $offset = 0, $limit = 0 );

    /**
     * Returns how many synonyms exist for a tag identified by $tagId
     *
     * @param int $tagId
     *
     * @return int
     */
    abstract public function getSynonymCount( $tagId );

    /**
     * Loads content IDs related to tag identified by $tagId
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of content IDs returned. If $limit = 0 all content IDs starting at $offset are returned
     *
     * @return int[]
     */
    abstract function getRelatedContentIds( $tagId, $offset = 0, $limit = 0 );

    /**
     * Returns the number of content objects related to tag identified by $tagId
     *
     * @param mixed $tagId
     *
     * @return int
     */
    abstract function getRelatedContentCount( $tagId );

    /**
     * Moves the synonym identified by $synonymId to tag identified by $mainTagData
     *
     * @param mixed $synonymId
     * @param array $mainTagData
     */
    abstract public function moveSynonym( $synonymId, $mainTagData );

    /**
     * Creates a new tag using the given $createStruct below $parentTag
     *
     * @param \EzSystems\TagsBundle\SPI\Persistence\Tags\CreateStruct $createStruct
     * @param array $parentTag
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag
     */
    abstract public function create( CreateStruct $createStruct, array $parentTag );

    /**
     * Updates an existing tag
     *
     * @param \EzSystems\TagsBundle\SPI\Persistence\Tags\UpdateStruct $updateStruct
     * @param mixed $tagId
     */
    abstract public function update( UpdateStruct $updateStruct, $tagId );

    /**
     * Creates a new synonym using the given $keyword for tag $tag
     *
     * @param string $keyword
     * @param array $tag
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag
     */
    abstract public function createSynonym( $keyword, array $tag );

    /**
     * Converts tag identified by $tagId to a synonym of tag identified by $mainTagData
     *
     * @param int $tagId
     * @param array $mainTagData
     */
    abstract public function convertToSynonym( $tagId, $mainTagData );

    /**
     * Moves a tag identified by $sourceTagData into new parent identified by $destinationParentTagData
     *
     * @param array $sourceTagData
     * @param array $destinationParentTagData
     */
    abstract public function moveSubtree( array $sourceTagData, array $destinationParentTagData );

    /**
     * Deletes tag identified by $tagId, including its synonyms and all tags under it
     *
     * If $tagId is a synonym, only the synonym is deleted
     *
     * @param mixed $tagId
     */
    abstract public function deleteTag( $tagId );

    /**
     * Updated subtree modification time for all tags in path
     *
     * @param string $pathString
     * @param int $timestamp
     */
    abstract public function updateSubtreeModificationTime( $pathString, $timestamp = null );
}
