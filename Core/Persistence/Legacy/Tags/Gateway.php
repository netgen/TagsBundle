<?php

namespace EzSystems\TagsBundle\Core\Persistence\Legacy\Tags;

abstract class Gateway
{
    /**
     * Returns an array with basic tag data
     *
     * @param mixed $tagId
     *
     * @return array
     */
    abstract function getBasicTagData( $tagId );

    /**
     * Returns an array with basic tag data for the tag with $remoteId
     *
     * @param string $remoteId
     *
     * @return array
     */
    abstract public function getBasicTagDataByRemoteId( $remoteId );

    /**
     * Updated subtree modification time for all tags in path
     *
     * @throws \RuntimeException
     *
     * @param string $pathString
     * @param int $timestamp
     */
    abstract function updateSubtreeModificationTime( $pathString, $timestamp = null );

    /**
     * Deletes tag identified by $tagId, including its synonyms and all tags under it
     *
     * If $tagId is a synonym, only the synonym is deleted
     *
     * @param mixed $tagId
     */
    abstract public function deleteTag( $tagId );
}
