<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use PDO;

abstract class Tags extends CriterionHandler
{
    /**
     * Returns searchable fields for the Criterion.
     *
     * @param string|null $fieldIdentifier
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given $fieldIdentifier
     *
     * @return int[]|null
     */
    protected function getSearchableFields(?string $fieldIdentifier = null): ?array
    {
        if ($fieldIdentifier === null) {
            return null;
        }

        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select($this->dbHandler->quoteColumn('id', 'ezcontentclass_attribute'))
            ->from($this->dbHandler->quoteTable('ezcontentclass_attribute'))
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn(
                            'is_searchable',
                            'ezcontentclass_attribute'
                        ),
                        $query->bindValue(1, null, PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn(
                            'data_type_string',
                            'ezcontentclass_attribute'
                        ),
                        $query->bindValue('eztags')
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('identifier', 'ezcontentclass_attribute'),
                        $query->bindValue($fieldIdentifier)
                    )
                )
            );

        $statement = $query->prepare();
        $statement->execute();
        $fieldDefinitionIds = $statement->fetchAll(PDO::FETCH_COLUMN);

        if (count($fieldDefinitionIds) === 0) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$fieldIdentifier}'."
            );
        }

        return array_map('intval', $fieldDefinitionIds);
    }
}
