<?php

namespace EzSystems\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway;

use EzSystems\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use RuntimeException;
use PDO;

class LegacyStorage extends Gateway
{
    /**
     * Connection
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected $connection;

    /**
     * Sets the data storage connection to use
     *
     * @throws \RuntimeException if $connection is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler}
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $connection
     */
    public function setConnection( $connection )
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if ( !$connection instanceof EzcDbHandler )
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
     * @return \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
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
        // TODO: Implement storeFieldData() method.
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
            )
        ;

        $query->prepare()->execute();
    }

    /**
     * Returns the data for the given $fieldId and $versionNo
     *
     * @param integer $fieldId
     * @param integer $versionNo
     *
     * @return array
     */
    protected function loadFieldData( $fieldId, $versionNo )
    {
        $connection = $this->getConnection();

        $query = $connection->createSelectQuery();
        $query
            ->selectDistinct( "*" )
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
        ;

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( PDO::FETCH_ASSOC );
    }
}
