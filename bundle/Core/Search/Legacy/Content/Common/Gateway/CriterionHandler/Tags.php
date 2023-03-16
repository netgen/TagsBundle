<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Types\Types;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use function array_map;
use function count;

abstract class Tags extends CriterionHandler
{
    /**
     * Returns searchable fields for the Criterion.
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given $fieldIdentifier
     *
     * @return int[]|null
     */
    protected function getSearchableFields(?string $fieldIdentifier = null): ?array
    {
        if ($fieldIdentifier === null) {
            return null;
        }

        $query = $this->connection->createQueryBuilder();
        $query
            ->select('ezcontentclass_attribute.id')
            ->from('ezcontentclass_attribute')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq(
                        'ezcontentclass_attribute.is_searchable',
                        ':is_searchable',
                    ),
                    $query->expr()->eq(
                        'ezcontentclass_attribute.data_type_string',
                        ':data_type_string',
                    ),
                    $query->expr()->eq(
                        'ezcontentclass_attribute.identifier',
                        ':identifier',
                    ),
                ),
            )->setParameter('is_searchable', 1, Types::INTEGER)
            ->setParameter('data_type_string', 'eztags', Types::STRING)
            ->setParameter('identifier', $fieldIdentifier, Types::STRING);

        $fieldDefinitionIds = $query->execute()->fetchAll(FetchMode::COLUMN);

        if (count($fieldDefinitionIds) === 0) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$fieldIdentifier}'.",
            );
        }

        return array_map('intval', $fieldDefinitionIds);
    }
}
