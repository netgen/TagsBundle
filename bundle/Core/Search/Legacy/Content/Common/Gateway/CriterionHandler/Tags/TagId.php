<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\Tags;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId as TagIdCriterion;
use Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\Tags;

final class TagId extends Tags
{
    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof TagIdCriterion;
    }

    public function handle(CriteriaConverter $converter, QueryBuilder $queryBuilder, Criterion $criterion, array $languageSettings): string
    {
        $subSelect = $this->connection->createQueryBuilder();
        $subSelect
            ->select('t1.id')
            ->from('ezcontentobject', 't1')
            ->innerJoin(
                't1',
                'eztags_attribute_link',
                't2',
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t2.objectattribute_version', 't1.current_version'),
                    $queryBuilder->expr()->eq('t2.object_id', 't1.id')
                )
            )->where(
                $queryBuilder->expr()->in(
                    't2.keyword_id',
                    $queryBuilder->createNamedParameter($criterion->value, Connection::PARAM_INT_ARRAY)
                )
            );

        $fieldDefinitionIds = $this->getSearchableFields($criterion->target);
        if ($fieldDefinitionIds !== null) {
            $subSelect->innerJoin(
                't2',
                'ezcontentobject_attribute',
                't3',
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t3.id', 't2.objectattribute_id'),
                    $queryBuilder->expr()->eq('t3.version', 't2.objectattribute_version')
                )
            )->andWhere(
                $queryBuilder->expr()->in(
                    't3.contentclassattribute_id',
                    $queryBuilder->createNamedParameter($fieldDefinitionIds, Connection::PARAM_INT_ARRAY)
                )
            );
        }

        return $queryBuilder->expr()->in('c.id', $subSelect->getSQL());
    }
}
