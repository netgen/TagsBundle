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
     * Updated subtree modification time for all tags in path
     *
     * @throws \RuntimeException
     *
     * @param string $pathString
     * @param int $timestamp
     */
    abstract public function updateSubtreeModificationTime( $pathString, $timestamp = null );

    /**
     * Deletes tag identified by $tagId, including its synonyms and all tags under it
     *
     * If $tagId is a synonym, only the synonym is deleted
     *
     * @param mixed $tagId
     */
    abstract public function deleteTag( $tagId );
}
