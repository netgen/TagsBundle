<?php

namespace Netgen\TagsBundle\Core\Search\Solr\Query\Common\SortClauseVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\SortClause\ContentTypeId as ContentTypeIdClause;

/**
 * Visits the sortClause tree into a Solr query.
 */
class ContentTypeId extends SortClauseVisitor
{
    /**
     * Check if visitor is applicable to current sortClause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return bool
     */
    public function canVisit(SortClause $sortClause)
    {
        return $sortClause instanceof ContentTypeIdClause;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return string
     */
    public function visit(SortClause $sortClause)
    {
        return 'content_type_id_id' . $this->getDirection($sortClause);
    }
}
