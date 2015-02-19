<?php

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagKeyword as TagKeywordCriterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Tag keyword criterion handler
 */
class TagKeyword extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof TagKeywordCriterion;
    }

    /**
     * Generate query expression for a Criterion this handler accepts
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle( CriteriaConverter $converter, SelectQuery $query, Criterion $criterion )
    {
        $subSelect = $query->subSelect();
        $subSelect
            ->select( $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ) )
            ->from( $this->dbHandler->quoteTable( 'ezcontentobject' ) )
            ->innerJoin(
                $this->dbHandler->quoteTable( 'eztags_attribute_link' ),
                $subSelect->expr->lAnd(
                    array(
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn( 'objectattribute_version', 'eztags_attribute_link' ),
                            $this->dbHandler->quoteColumn( 'current_version', 'ezcontentobject' )
                        ),
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn( 'object_id', 'eztags_attribute_link' ),
                            $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' )
                        )
                    )
                )
            )->innerJoin(
                $this->dbHandler->quoteTable( 'eztags' ),
                $subSelect->expr->eq(
                    $this->dbHandler->quoteColumn( 'keyword_id', 'eztags_attribute_link' ),
                    $this->dbHandler->quoteColumn( 'id', 'eztags' )
                )
            );

        if ( $criterion->operator == Criterion\Operator::LIKE )
        {
            $subSelect->where(
                $query->expr->like(
                    $this->dbHandler->quoteColumn( 'keyword', 'eztags' ),
                    $query->bindValue( $criterion->value[0] )
                )
            );
        }
        else
        {
            $subSelect->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn( 'keyword', 'eztags' ),
                    $criterion->value
                )
            );
        }

        return $query->expr->in(
            $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
            $subSelect
        );
    }
}

