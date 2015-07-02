<?php

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;

use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Doctrine\DBAL\DBALException;
use PDOException;
use RuntimeException;

class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway
     *
     * @var \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway
     *
     * @param \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway $innerGateway
     */
    public function __construct( Gateway $innerGateway )
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * Returns an array with basic tag data
     *
     * @throws \RuntimeException
     *
     * @param mixed $tagId
     *
     * @return array
     */
    public function getBasicTagData( $tagId )
    {
        try
        {
            return $this->innerGateway->getBasicTagData( $tagId );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Returns an array with full tag data
     *
     * @throws \RuntimeException
     *
     * @param mixed $tagId
     * @param string[] $translations
     *
     * @return array
     */
    public function getFullTagData( $tagId, array $translations = null )
    {
        try
        {
            return $this->innerGateway->getFullTagData( $tagId, $translations );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Returns an array with basic tag data for the tag with $remoteId
     *
     * @throws \RuntimeException
     *
     * @param string $remoteId
     *
     * @return array
     */
    public function getFullTagDataByRemoteId( $remoteId )
    {
        try
        {
            return $this->innerGateway->getFullTagDataByRemoteId( $remoteId );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Returns an array with basic tag data for the tag with $url
     *
     * @throws \RuntimeException
     *
     * @param string $url
     *
     * @return array
     */
    public function getBasicTagDataByUrl( $url )
    {
        try
        {
            return $this->innerGateway->getBasicTagDataByUrl( $url );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Returns data for the first level children of the tag identified by given $tagId
     *
     * @throws \RuntimeException
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all children starting at $offset are returned
     *
     * @return array
     */
    public function getChildren( $tagId, $offset = 0, $limit = -1 )
    {
        try
        {
            return $this->innerGateway->getChildren( $tagId, $offset, $limit );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Returns how many tags exist below tag identified by $tagId
     *
     * @throws \RuntimeException
     *
     * @param mixed $tagId
     *
     * @return int
     */
    public function getChildrenCount( $tagId )
    {
        try
        {
            return $this->innerGateway->getChildrenCount( $tagId );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Returns data for tags identified by given $keyword
     *
     * @throws \RuntimeException
     *
     * @param string $keyword
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all tags starting at $offset are returned
     *
     * @return array
     */
    public function getTagsByKeyword( $keyword, $offset = 0, $limit = -1 )
    {
        try
        {
            return $this->innerGateway->getTagsByKeyword( $keyword, $offset, $limit );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Returns how many tags exist with $keyword
     *
     * @throws \RuntimeException
     *
     * @param string $keyword
     *
     * @return int
     */
    public function getTagsByKeywordCount( $keyword )
    {
        try
        {
            return $this->innerGateway->getTagsByKeywordCount( $keyword );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Returns data for synonyms of the tag identified by given $tagId
     *
     * @throws \RuntimeException
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all synonyms starting at $offset are returned
     *
     * @return array
     */
    public function getSynonyms( $tagId, $offset = 0, $limit = -1 )
    {
        try
        {
            return $this->innerGateway->getSynonyms( $tagId, $offset, $limit );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Returns how many synonyms exist for a tag identified by $tagId
     *
     * @throws \RuntimeException
     *
     * @param mixed $tagId
     *
     * @return int
     */
    public function getSynonymCount( $tagId )
    {
        try
        {
            return $this->innerGateway->getSynonymCount( $tagId );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Loads content IDs related to tag identified by $tagId
     *
     * @throws \RuntimeException
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of content IDs returned. If $limit = -1 all content IDs starting at $offset are returned
     *
     * @return array
     */
    function getRelatedContentIds( $tagId, $offset = 0, $limit = -1 )
    {
        try
        {
            return $this->innerGateway->getRelatedContentIds( $tagId, $offset, $limit );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Returns the number of content objects related to tag identified by $tagId
     *
     * @throws \RuntimeException
     *
     * @param mixed $tagId
     *
     * @return int
     */
    function getRelatedContentCount( $tagId )
    {
        try
        {
            return $this->innerGateway->getRelatedContentCount( $tagId );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Moves the synonym identified by $synonymId to tag identified by $mainTagData
     *
     * @throws \RuntimeException
     *
     * @param mixed $synonymId
     * @param array $mainTagData
     */
    public function moveSynonym( $synonymId, $mainTagData )
    {
        try
        {
            return $this->innerGateway->moveSynonym( $synonymId, $mainTagData );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Creates a new tag using the given $createStruct below $parentTag
     *
     * @throws \RuntimeException
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct $createStruct
     * @param array $parentTag
     *
     * @return int
     */
    public function create( CreateStruct $createStruct, array $parentTag = null )
    {
        try
        {
            return $this->innerGateway->create( $createStruct, $parentTag );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Updates an existing tag
     *
     * @throws \RuntimeException
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct $updateStruct
     * @param mixed $tagId
     */
    public function update( UpdateStruct $updateStruct, $tagId )
    {
        try
        {
            $this->innerGateway->update( $updateStruct, $tagId );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Creates a new synonym using the given $keyword for tag $tag
     *
     * @throws \RuntimeException
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct $createStruct
     * @param array $tag
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function createSynonym( SynonymCreateStruct $createStruct, array $tag )
    {
        try
        {
            return $this->innerGateway->createSynonym( $createStruct, $tag );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Converts tag identified by $tagId to a synonym of tag identified by $mainTagData
     *
     * @throws \RuntimeException
     *
     * @param mixed $tagId
     * @param array $mainTagData
     */
    public function convertToSynonym( $tagId, $mainTagData )
    {
        try
        {
            return $this->innerGateway->convertToSynonym( $tagId, $mainTagData );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Transfers all tag attribute links from tag identified by $tagId into the tag identified by $targetTagId
     *
     * @throws \RuntimeException
     *
     * @param mixed $tagId
     * @param mixed $targetTagId
     */
    public function transferTagAttributeLinks( $tagId, $targetTagId )
    {
        try
        {
            $this->innerGateway->transferTagAttributeLinks( $tagId, $targetTagId );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Moves a tag identified by $sourceTagData into new parent identified by $destinationParentTagData
     *
     * @throws \RuntimeException
     *
     * @param array $sourceTagData
     * @param array $destinationParentTagData
     *
     * @return array Tag data of the updated root tag
     */
    public function moveSubtree( array $sourceTagData, array $destinationParentTagData )
    {
        try
        {
            return $this->innerGateway->moveSubtree( $sourceTagData, $destinationParentTagData );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Deletes tag identified by $tagId, including its synonyms and all tags under it
     *
     * @throws \RuntimeException
     *
     * If $tagId is a synonym, only the synonym is deleted
     *
     * @param mixed $tagId
     */
    public function deleteTag( $tagId )
    {
        try
        {
            $this->innerGateway->deleteTag( $tagId );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }

    /**
     * Updated subtree modification time for all tags in path
     *
     * @throws \RuntimeException
     *
     * @param string $pathString
     * @param int $timestamp
     */
    public function updateSubtreeModificationTime( $pathString, $timestamp = null )
    {
        try
        {
            $this->innerGateway->updateSubtreeModificationTime( $pathString, $timestamp );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }
}
