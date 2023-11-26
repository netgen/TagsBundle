<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\Tags;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagSubtree as TagSubtreeCriterion;
use Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\Tags;

final class TagSubtree extends Tags
{
    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof TagSubtreeCriterion;
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
            )
            ->innerJoin(
                't2',
                'eztags',
                't3',
                $queryBuilder->expr()->eq('t2.keyword_id', 't3.id')
            );


        if (is_array($criterion->value)) {
            foreach ($criterion->value as $value) {
                $subSelect->orWhere(
                    $queryBuilder->expr()->like(
                        't3.path_string',
                        $queryBuilder->createNamedParameter('%/' . $value . '/%')
                    )
                );
            }
        }

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
