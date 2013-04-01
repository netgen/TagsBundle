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
            );

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
            );

        $statement = $query->prepare();
        $statement->execute();

        if ( $row = $statement->fetch( PDO::FETCH_ASSOC ) )
        {
            return $row;
        }

        throw new NotFoundException( "tag", $remoteId );
    }

    /**
     * Returns data for the first level children of the tag identified by given $tagId
     *
     * @param mixed $tagId
     * @param integer $offset The start offset for paging
     * @param integer $limit The number of tags returned. If $limit = 0 all children starting at $offset are returned
     *
     * @return array
     */
    public function getChildren( $tagId, $offset = 0, $limit = 0 )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( "*" )
            ->from( $this->handler->quoteTable( "eztags" ) )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->handler->quoteColumn( "parent_id", "eztags" ),
                        $query->bindValue( $tagId, null, PDO::PARAM_INT )
                    ),
                    $query->expr->eq( $this->handler->quoteColumn( "main_tag_id", "eztags" ), 0 )
                )
            )
            ->limit( $limit > 0 ? $limit : PHP_INT_MAX, $offset );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( PDO::FETCH_ASSOC );
    }

    /**
     * Returns how many tags exist below tag identified by $tagId
     *
     * @param int $tagId
     *
     * @return int
     */
    public function getChildrenCount( $tagId )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select(
                $query->alias( $query->expr->count( "*" ), "count" )
            )
            ->from( $this->handler->quoteTable( "eztags" ) )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->handler->quoteColumn( "parent_id", "eztags" ),
                        $query->bindValue( $tagId, null, PDO::PARAM_INT )
                    ),
                    $query->expr->eq( $this->handler->quoteColumn( "main_tag_id", "eztags" ), 0 )
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( PDO::FETCH_ASSOC );

        return (int)$rows[0]["count"];
    }

    /**
     * Returns data for synonyms of the tag identified by given $tagId
     *
     * @param mixed $tagId
     * @param integer $offset The start offset for paging
     * @param integer $limit The number of tags returned. If $limit = 0 all synonyms starting at $offset are returned
     *
     * @return array
     */
    public function getSynonyms( $tagId, $offset = 0, $limit = 0 )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( "*" )
            ->from( $this->handler->quoteTable( "eztags" ) )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( "main_tag_id", "eztags" ),
                    $query->bindValue( $tagId, null, PDO::PARAM_INT )
                )
            )
            ->limit( $limit > 0 ? $limit : PHP_INT_MAX, $offset );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( PDO::FETCH_ASSOC );
    }

    /**
     * Returns how many synonyms exist for a tag identified by $tagId
     *
     * @param int $tagId
     *
     * @return int
     */
    public function getSynonymCount( $tagId )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select(
                $query->alias( $query->expr->count( "*" ), "count" )
            )
            ->from( $this->handler->quoteTable( "eztags" ) )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( "main_tag_id", "eztags" ),
                    $query->bindValue( $tagId, null, PDO::PARAM_INT )
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( PDO::FETCH_ASSOC );

        return (int)$rows[0]["count"];
    }

    /**
     * Loads content IDs related to tag identified by $tagId
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of content IDs returned. If $limit = 0 all content IDs starting at $offset are returned
     *
     * @return int[]
     */
    function getRelatedContentIds( $tagId, $offset = 0, $limit = 0 )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->selectDistinct(
                $this->handler->quoteColumn( "object_id", "eztags_attribute_link" )
            )
            ->from( $this->handler->quoteTable( "eztags_attribute_link" ) )
            ->innerJoin(
                $this->handler->quoteTable( "ezcontentobject" ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->handler->quoteColumn( "object_id", "eztags_attribute_link" ),
                        $this->handler->quoteColumn( "id", "ezcontentobject" )
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn( "objectattribute_version", "eztags_attribute_link" ),
                        $this->handler->quoteColumn( "current_version", "ezcontentobject" )
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn( "status", "ezcontentobject" ),
                        1
                    )
                )
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( "keyword_id", "eztags_attribute_link" ),
                    $query->bindValue( $tagId, null, PDO::PARAM_INT )
                )
            )->limit( $limit > 0 ? $limit : PHP_INT_MAX, $offset );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( PDO::FETCH_ASSOC );

        $contentIds = array();
        foreach ( $rows as $row )
        {
            $contentIds[] = (int)$row["object_id"];
        }

        return $contentIds;
    }

    /**
     * Returns the number of content objects related to tag identified by $tagId
     *
     * @param mixed $tagId
     *
     * @return int
     */
    function getRelatedContentCount( $tagId )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->selectDistinct(
                $query->alias(
                    $query->expr->count(
                        $this->handler->quoteColumn( "object_id", "eztags_attribute_link" )
                    ),
                    "count"
                )
            )
            ->from( $this->handler->quoteTable( "eztags_attribute_link" ) )
            ->innerJoin(
                $this->handler->quoteTable( "ezcontentobject" ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->handler->quoteColumn( "object_id", "eztags_attribute_link" ),
                        $this->handler->quoteColumn( "id", "ezcontentobject" )
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn( "objectattribute_version", "eztags_attribute_link" ),
                        $this->handler->quoteColumn( "current_version", "ezcontentobject" )
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn( "status", "ezcontentobject" ),
                        1
                    )
                )
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( "keyword_id", "eztags_attribute_link" ),
                    $query->bindValue( $tagId, null, PDO::PARAM_INT )
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( PDO::FETCH_ASSOC );

        return (int)$rows[0]["count"];
    }

    /**
     * Moves the synonym identified by $synonymId to tag identified by $mainTagData
     *
     * @param mixed $synonymId
     * @param array $mainTagData
     */
    public function moveSynonym( $synonymId, $mainTagData )
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( "eztags" ) )
            ->set(
                $this->handler->quoteColumn( "parent_id" ),
                $query->bindValue( $mainTagData["parent_id"], null, PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( "main_tag_id" ),
                $query->bindValue( $mainTagData["id"], null, PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( "depth" ),
                $query->bindValue( $mainTagData["depth"], null, PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( "path_string" ),
                $query->bindValue( $this->getSynonymPathString( $synonymId, $mainTagData["path_string"] ), null, PDO::PARAM_STR )
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( "id" ),
                    $query->bindValue( $synonymId, null, PDO::PARAM_INT )
                )
            );

        $query->prepare()->execute();
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
            );

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
            );

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
            );

        $query->prepare()->execute();
    }

    /**
     * Creates a new synonym using the given $keyword for tag $tag
     *
     * @param string $keyword
     * @param array $tag
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function createSynonym( $keyword, array $tag )
    {
        $synonym = new Tag();

        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto( $this->handler->quoteTable( "eztags" ) )
            ->set(
                $this->handler->quoteColumn( "id" ),
                $this->handler->getAutoIncrementValue( "eztags", "id" )
            )->set(
                $this->handler->quoteColumn( "parent_id" ),
                $query->bindValue( $synonym->parentTagId = $tag["parent_id"], null, PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( "main_tag_id" ),
                $query->bindValue( $synonym->mainTagId = $tag["id"], null, PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( "keyword" ),
                $query->bindValue( $synonym->keyword = $keyword, null, PDO::PARAM_STR )
            )->set(
                $this->handler->quoteColumn( "depth" ),
                $query->bindValue( $synonym->depth = $tag["depth"], null, PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( "path_string" ),
                $query->bindValue( "dummy" ) // Set later
            )->set(
                $this->handler->quoteColumn( "modified" ),
                $query->bindValue( $synonym->modificationDate = time(), null, PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( "remote_id" ),
                $query->bindValue( $synonym->remoteId = md5( uniqid( get_class( $this ), true ) ), null, PDO::PARAM_STR )
            );

        $query->prepare()->execute();

        $synonym->id = $this->handler->lastInsertId( $this->handler->getSequenceName( "eztags", "id" ) );
        $synonym->pathString = $this->getSynonymPathString( $synonym->id, $tag["path_string"] );

        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( "eztags" ) )
            ->set(
                $this->handler->quoteColumn( "path_string" ),
                $query->bindValue( $synonym->pathString, null, PDO::PARAM_STR )
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( "id" ),
                    $query->bindValue( $synonym->id, null, PDO::PARAM_INT )
                )
            );

        $query->prepare()->execute();

        return $synonym;
    }

    /**
     * Converts tag identified by $tagId to a synonym of tag identified by $mainTagData
     *
     * @param int $tagId
     * @param array $mainTagData
     */
    public function convertToSynonym( $tagId, $mainTagData )
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( "eztags" ) )
            ->set(
                $this->handler->quoteColumn( "parent_id" ),
                $query->bindValue( $mainTagData["parent_id"], null, PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( "main_tag_id" ),
                $query->bindValue( $mainTagData["id"], null, PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( "depth" ),
                $query->bindValue( $mainTagData["depth"], null, PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( "path_string" ),
                $query->bindValue( $this->getSynonymPathString( $tagId, $mainTagData["path_string"] ), null, PDO::PARAM_STR )
            )->set(
                $this->handler->quoteColumn( "modified" ),
                $query->bindValue( time(), null, PDO::PARAM_INT )
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( "id" ),
                    $query->bindValue( $tagId, null, PDO::PARAM_INT )
                )
            );

        $query->prepare()->execute();
    }

    /**
     * Moves a tag identified by $sourceTagData into new parent identified by $destinationParentTagData
     *
     * @param array $sourceTagData
     * @param array $destinationParentTagData
     */
    public function moveSubtree( array $sourceTagData, array $destinationParentTagData )
    {
        $fromPathString = $sourceTagData["path_string"];

        $query = $this->handler->createSelectQuery();
        $query
            ->select(
                $this->handler->quoteColumn( "id" ),
                $this->handler->quoteColumn( "parent_id" ),
                $this->handler->quoteColumn( "main_tag_id" ),
                $this->handler->quoteColumn( "path_string" )
            )
            ->from( $this->handler->quoteTable( "eztags" ) )
            ->where(
                $query->expr->lOr(
                    $query->expr->like(
                        $this->handler->quoteColumn( "path_string" ),
                        $query->bindValue( $fromPathString . "%", null, PDO::PARAM_STR )
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn( "main_tag_id" ),
                        $query->bindValue( $sourceTagData["id"], null, PDO::PARAM_INT )
                    )
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( PDO::FETCH_ASSOC );

        $oldParentPathString = implode( "/", array_slice( explode( "/", $fromPathString ), 0, -2 ) ) . "/";
        foreach ( $rows as $row )
        {
            // Prefixing ensures correct replacement when there is no parent
            $newPathString = str_replace(
                "prefix" . $oldParentPathString,
                $destinationParentTagData["path_string"],
                "prefix" . $row["path_string"]
            );

            $newParentId = $row["parent_id"];
            if ( $row["path_string"] === $fromPathString || $row["main_tag_id"] == $sourceTagData["id"] )
            {
                $newParentId = (int)implode( "", array_slice( explode( "/", $newPathString ), -3, 1 ) );
            }

            $query = $this->handler->createUpdateQuery();
            $query
                ->update( $this->handler->quoteTable( "eztags" ) )
                ->set(
                    $this->handler->quoteColumn( "path_string" ),
                    $query->bindValue( $newPathString )
                )->set(
                    $this->handler->quoteColumn( "depth" ),
                    $query->bindValue( substr_count( $newPathString, "/" ) - 1 )
                )->set(
                    $this->handler->quoteColumn( "parent_id" ),
                    $query->bindValue( $newParentId )
                )->where(
                    $query->expr->eq(
                        $this->handler->quoteColumn( "id" ),
                        $query->bindValue( $row["id"], null, PDO::PARAM_INT )
                    )
                );

            $query->prepare()->execute();
        }
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
                        $query->bindValue( "%/" . (int)$tagId . "/%", null, PDO::PARAM_STR )
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn( "main_tag_id" ),
                        $query->bindValue( $tagId, null, PDO::PARAM_INT )
                    )
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        $tagIds = array();
        while ( $row = $statement->fetch( PDO::FETCH_ASSOC ) )
        {
            $tagIds[] = (int)$row["id"];
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
            );

        $query->prepare()->execute();

        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom( $this->handler->quoteTable( "eztags" ) )
            ->where(
                $query->expr->in(
                    $this->handler->quoteColumn( "id" ),
                    $tagIds
                )
            );

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
            );

        $query->prepare()->execute();
    }

    /**
     * Returns the path string of a synonym for main tag path string
     *
     * @param mixed $synonymId
     * @param string $mainTagPathString
     *
     * @return string
     */
    protected function getSynonymPathString( $synonymId, $mainTagPathString )
    {
        $pathStringElements = explode( "/", trim( $mainTagPathString, "/" ) );
        array_pop( $pathStringElements );

        return ( !empty( $pathStringElements ) ? "/" . implode( "/", $pathStringElements ) : "" ) . "/" . (int)$synonymId . "/";
    }
}
