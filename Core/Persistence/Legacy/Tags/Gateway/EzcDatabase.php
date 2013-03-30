<?php

namespace EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;

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
}
