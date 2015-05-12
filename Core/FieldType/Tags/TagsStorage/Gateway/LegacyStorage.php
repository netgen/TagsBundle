<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway;

use Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use RuntimeException;
use PDO;

class LegacyStorage extends Gateway
{
    /**
     * Connection
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $connection;

    /**
     * Sets the data storage connection to use
     *
     * @throws \RuntimeException if $connection is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Database\DatabaseHandler}
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $connection
     */
    public function setConnection( $connection )
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if ( !$connection instanceof DatabaseHandler )
        {
            throw new RuntimeException( "Invalid connection passed" );
        }

        $this->connection = $connection;
    }

    /**
     * Returns the active connection
     *
     * @throws \RuntimeException if no connection has been set, yet.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected function getConnection()
    {
        if ( $this->connection === null )
        {
            throw new RuntimeException( "Missing database connection." );
        }

        return $this->connection;
    }

    /**
     * Stores the tags in the database based on the given field data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field )
    {
        $connection = $this->getConnection();

        foreach ( $field->value->externalData as $priority => $tag )
        {
            $insertQuery = $connection->createInsertQuery();
            $insertQuery
                ->insertInto( $connection->quoteTable( "eztags_attribute_link" ) )
                ->set(
                    $connection->quoteColumn( "keyword_id" ),
                    $insertQuery->bindValue( $tag["id"], null, PDO::PARAM_INT )
                )->set(
                    $connection->quoteColumn( "objectattribute_id" ),
                    $insertQuery->bindValue( $field->id, null, PDO::PARAM_INT )
                )->set(
                    $connection->quoteColumn( "objectattribute_version" ),
                    $insertQuery->bindValue( $versionInfo->versionNo, null, PDO::PARAM_INT )
                )->set(
                    $connection->quoteColumn( "object_id" ),
                    $insertQuery->bindValue( $versionInfo->contentInfo->id, null, PDO::PARAM_INT )
                )->set(
                    $connection->quoteColumn( "priority" ),
                    $insertQuery->bindValue( $priority, null, PDO::PARAM_INT )
                );

            $insertQuery->prepare()->execute();
        }
    }

    /**
     * Gets the tags stored in the field
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field )
    {
        $field->value->externalData = $this->loadFieldData( $field->id, $versionInfo->versionNo );
    }

    /**
     * Deletes field data for all $fieldIds in the version identified by
     * $versionInfo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds
     */
    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds )
    {
        $connection = $this->getConnection();

        $query = $connection->createDeleteQuery();
        $query
            ->deleteFrom( $connection->quoteTable( "eztags_attribute_link" ) )
            ->where(
                $query->expr->lAnd(
                    $query->expr->in(
                        $connection->quoteColumn( "objectattribute_id" ),
                        $fieldIds
                    ),
                    $query->expr->eq(
                        $connection->quoteColumn( "objectattribute_version" ),
                        $query->bindValue( $versionInfo->versionNo, null, PDO::PARAM_INT )
                    )
                )
            );

        $query->prepare()->execute();
    }

    /**
     * Returns the data for the given $fieldId and $versionNo
     *
     * @param mixed $fieldId
     * @param mixed $versionNo
     *
     * @return array
     */
    protected function loadFieldData( $fieldId, $versionNo )
    {
        $connection = $this->getConnection();

        $query = $connection->createSelectQuery();
        $query
            ->selectDistinct( "eztags.*", $connection->quoteColumn( "priority", "eztags_attribute_link" ) )
            ->from( $connection->quoteTable( "eztags" ) )
            ->innerJoin(
                $connection->quoteTable( "eztags_attribute_link" ),
                $query->expr->eq(
                    $connection->quoteColumn( "id", "eztags" ),
                    $connection->quoteColumn( "keyword_id", "eztags_attribute_link" )
                )
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $connection->quoteColumn( "objectattribute_id", "eztags_attribute_link" ),
                        $query->bindValue( $fieldId, null, PDO::PARAM_INT )
                    ),
                    $query->expr->eq(
                        $connection->quoteColumn( "objectattribute_version", "eztags_attribute_link" ),
                        $query->bindValue( $versionNo, null, PDO::PARAM_INT )
                    )
                )
            )
            ->orderBy( $connection->quoteColumn( "priority", "eztags_attribute_link" ) );

        $statement = $query->prepare();
        $statement->execute();

        // Remove 'priority' column added by pgsql requirement
        // that all columns used in ORDER BY must be in SELECT
        $rows = $statement->fetchAll( PDO::FETCH_ASSOC );
        foreach ( array_keys( $rows ) as $key )
        {
            unset( $rows[$key]["priority"] );
        }

        return $rows;
    }
}
