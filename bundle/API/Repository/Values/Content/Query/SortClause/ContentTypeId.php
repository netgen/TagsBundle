<?php

namespace Netgen\TagsBundle\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Sets sort direction on Content Type ID for a content query.
 */
class ContentTypeId extends SortClause
{
    /**
     * Constructs a new ContentTypeId SortClause.
     *
     * @param string $sortDirection
     */
    public function __construct($sortDirection = Query::SORT_ASC)
    {
        parent::__construct('content_type_id', $sortDirection);
    }
}
