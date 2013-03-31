<?php

namespace EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;

use EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;
use EzSystems\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use EzSystems\TagsBundle\SPI\Persistence\Tags\UpdateStruct;
use ezcDbException;
use PDOException;
use RuntimeException;

class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway
     *
     * @var \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway
     *
     * @param \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway $innerGateway
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
        catch ( ezcDbException $e )
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
    public function getBasicTagDataByRemoteId( $remoteId )
    {
        try
        {
            return $this->innerGateway->getBasicTagDataByRemoteId( $remoteId );
        }
        catch ( ezcDbException $e )
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
     * @param \EzSystems\TagsBundle\SPI\Persistence\Tags\CreateStruct $createStruct
     * @param array $parentTag
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function create( CreateStruct $createStruct, array $parentTag )
    {
        try
        {
            return $this->innerGateway->create( $createStruct, $parentTag );
        }
        catch ( ezcDbException $e )
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
     * @param \EzSystems\TagsBundle\SPI\Persistence\Tags\UpdateStruct $updateStruct
     * @param mixed $tagId
     */
    public function update( UpdateStruct $updateStruct, $tagId )
    {
        try
        {
            $this->innerGateway->update( $updateStruct, $tagId );
        }
        catch ( ezcDbException $e )
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
        catch ( ezcDbException $e )
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
        catch ( ezcDbException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( "Database error", 0, $e );
        }
    }
}
