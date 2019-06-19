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
    public function canVisit(SortClause $sortClause): bool
    {
        return $sortClause instanceof ContentTypeIdClause;
    }

    public function visit(SortClause $sortClause): string
    {
        return 'content_type_id_id' . $this->getDirection($sortClause);
    }
}
