<?php

namespace EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;

use EzSystems\TagsBundle\SPI\Persistence\Tags\Tag;
use EzSystems\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use EzSystems\TagsBundle\SPI\Persistence\Tags\UpdateStruct;
use EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use PDO;

class EzcDatabase extends Gateway
{
    /**
     * Database handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected $handler;

    /**
     * Construct from database handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $handler
     */
    public function __construct( EzcDbHandler $handler )
    {
        $this->handler = $handler;
    }

    /**
     * Returns an array with basic tag data
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @param mixed $tagId
     *
     * @return array
     */
    public function getBasicTagData( $tagId )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( "*" )
            ->from( $this->handler->quoteTable( "eztags" ) )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( "id" ),
                    $query->bindValue( $tagId, null, PDO::PARAM_INT )
                )
            )
        ;

        $statement = $query->prepare();
        $statement->execute();

        if ( $row = $statement->fetch( PDO::FETCH_ASSOC ) )
        {
            return $row;
        }

        throw new NotFoundException( "tag", $tagId );
    }

    /**
     * Returns an array with basic tag data for the tag with $remoteId
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @param string $remoteId
     *
     * @return array
     */
    public function getBasicTagDataByRemoteId( $remoteId )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( "*" )
            ->from( $this->handler->quoteTable( "eztags" ) )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( "remote_id" ),
                    $query->bindValue( $remoteId, null, PDO::PARAM_STR )
                )
            )
        ;

        $statement = $query->prepare();
        $statement->execute();

        if ( $row = $statement->fetch( PDO::FETCH_ASSOC ) )
        {
            return $row;
        }

        throw new NotFoundException( "tag", $remoteId );
    }

    /**
     * Creates a new tag using the given $createStruct below $parentTag
     *
     * @param \EzSystems\TagsBundle\SPI\Persistence\Tags\CreateStruct $createStruct
     * @param array $parentTag
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function create( CreateStruct $createStruct, array $parentTag )
    {
        $tag = new Tag();

        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto( $this->handler->quoteTable( "eztags" ) )
            ->set(
                $this->handler->quoteColumn( "id" ),
                $this->handler->getAutoIncrementValue( "eztags", "id" )
            )->set(
                $this->handler->quoteColumn( "parent_id" ),
                $query->bindValue( $tag->parentTagId = $parentTag["id"], null, PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( "main_tag_id" ),
                $query->bindValue( $tag->mainTagId = 0, null, PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( "keyword" ),
                $query->bindValue( $tag->keyword = $createStruct->keyword, null, PDO::PARAM_STR )
            )->set(
                $this->handler->quoteColumn( "depth" ),
                $query->bindValue( $tag->depth = $parentTag["depth"] + 1, null, PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( "path_string" ),
                $query->bindValue( "dummy" ) // Set later
            )->set(
                $this->handler->quoteColumn( "modified" ),
                $query->bindValue( $tag->modificationDate = time(), null, PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( "remote_id" ),
                $query->bindValue( $tag->remoteId = $createStruct->remoteId, null, PDO::PARAM_STR )
            )
        ;

        $query->prepare()->execute();

        $tag->id = $this->handler->lastInsertId( $this->handler->getSequenceName( "eztags", "id" ) );
        $tag->pathString = $parentTag["path_string"] . $tag->id . "/";

        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( "eztags" ) )
            ->set(
                $this->handler->quoteColumn( "path_string" ),
                $query->bindValue( $tag->pathString, null, PDO::PARAM_STR )
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( "id" ),
                    $query->bindValue( $tag->id, null, PDO::PARAM_INT )
                )
            )
        ;

        $query->prepare()->execute();

        return $tag;
    }

    /**
     * Updates an existing tag
     *
     * @param \EzSystems\TagsBundle\SPI\Persistence\Tags\UpdateStruct $updateStruct
     * @param mixed $tagId
     */
    public function update( UpdateStruct $updateStruct, $tagId )
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( "eztags" ) )
            ->set(
                $this->handler->quoteColumn( "keyword" ),
                $query->bindValue( $updateStruct->keyword, null, PDO::PARAM_STR )
            )->set(
                $this->handler->quoteColumn( "remote_id" ),
                $query->bindValue( $updateStruct->remoteId, null, PDO::PARAM_STR )
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( "id" ),
                    $query->bindValue( $tagId, null, PDO::PARAM_INT )
                )
            )
        ;

        $query->prepare()->execute();
    }

    /**
     * Updated subtree modification time for all tags in path
     *
     * @param string $pathString
     * @param int $timestamp
     */
    public function updateSubtreeModificationTime( $pathString, $timestamp = null )
    {
        $tagIds = array_filter( explode( "/", $pathString ) );

        if ( empty( $tagIds ) )
        {
            return;
        }

        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( "eztags" ) )
            ->set(
                $this->handler->quoteColumn( "modified" ),
                $query->bindValue( $timestamp ?: time(), null, PDO::PARAM_INT )
            )
            ->where(
                $query->expr->in(
                    $this->handler->quoteColumn( "id" ),
                    $tagIds
                )
            )
        ;

        $query->prepare()->execute();
    }

    /**
     * Deletes tag identified by $tagId, including its synonyms and all tags under it
     *
     * If $tagId is a synonym, only the synonym is deleted
     *
     * @param mixed $tagId
     */
    public function deleteTag( $tagId )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( "id" )
            ->from( $this->handler->quoteTable( "eztags" ) )
            ->where(
                $query->expr->lOr(
                    $query->expr->like(
                        $this->handler->quoteColumn( "path_string" ),
                        $query->bindValue( "%/" . (int) $tagId . "/%", null, PDO::PARAM_STR )
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn( "main_tag_id" ),
                        $query->bindValue( $tagId, null, PDO::PARAM_INT )
                    )
                )
            )
        ;

        $statement = $query->prepare();
        $statement->execute();

        $tagIds = array();
        while ( $row = $statement->fetch( PDO::FETCH_ASSOC ) )
        {
            $tagIds[] = (int) $row["id"];
        }

        if ( empty( $tagIds ) )
        {
            return;
        }

        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom( $this->handler->quoteTable( "eztags_attribute_link" ) )
            ->where(
                $query->expr->in(
                    $this->handler->quoteColumn( "keyword_id" ),
                    $tagIds
                )
            )
        ;

        $query->prepare()->execute();

        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom( $this->handler->quoteTable( "eztags" ) )
            ->where(
                $query->expr->in(
                    $this->handler->quoteColumn( "id" ),
                    $tagIds
                )
            )
        ;

        $query->prepare()->execute();
    }
}
