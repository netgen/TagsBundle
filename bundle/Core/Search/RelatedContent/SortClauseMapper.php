<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Search\RelatedContent;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\SortClause\ContentTypeId;

final class SortClauseMapper
{
    /**
     * @var array
     */
    private static $allowedSortOptions = [
        'content_id_ascending',
        'content_id_descending',
        'name_ascending',
        'name_descending',
        'date_modified_ascending',
        'date_modified_descending',
        'content_type_id_ascending',
        'content_type_id_descending',
    ];

    /**
     * Returns allowed sort options.
     */
    public function getSortOptions(): array
    {
        return self::$allowedSortOptions;
    }

    /**
     * Maps given sort options to corresponding SortClause objects, if supported.
     *
     * @param array $sortOptions
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    public function mapSortClauses(array $sortOptions): array
    {
        $sortClauses = [];

        foreach ($sortOptions as $sortOption) {
            if (!in_array($sortOption, self::$allowedSortOptions, true)) {
                continue;
            }

            $sortClauses[] = $this->mapSortClause($sortOption);
        }

        return $sortClauses;
    }

    /**
     * Maps given sort option to corresponding SortClause, if supported.
     */
    private function mapSortClause(string $sortOption): SortClause
    {
        switch ($sortOption) {
            case 'content_id_ascending':
                return new SortClause\ContentId(Query::SORT_ASC);
            case 'content_id_desc':
                return new SortClause\ContentId(Query::SORT_DESC);
            case 'name_ascending':
                return new SortClause\ContentName(Query::SORT_ASC);
            case 'name_descending':
                return new SortClause\ContentName(Query::SORT_DESC);
            case 'content_type_id_ascending':
                return new ContentTypeId(Query::SORT_ASC);
            case 'content_type_id_descending':
                return new ContentTypeId(Query::SORT_DESC);
            case 'date_modified_ascending':
                return new SortClause\DateModified(Query::SORT_ASC);
            case 'date_modified_descending':
            default:
                return new SortClause\DateModified(Query::SORT_DESC);
        }
    }
}
