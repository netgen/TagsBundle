<?php

namespace Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\Tags;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId as TagIdCriterion;
use Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\Tags;

class TagId extends Tags
{
    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof TagIdCriterion;
    }

    public function handle(CriteriaConverter $converter, SelectQuery $query, Criterion $criterion, ?array $fieldFilters = null): string
    {
        $subSelect = $query->subSelect();
        $subSelect
            ->select($this->dbHandler->quoteColumn('id', 'ezcontentobject'))
            ->from($this->dbHandler->quoteTable('ezcontentobject'))
            ->innerJoin(
                $this->dbHandler->quoteTable('eztags_attribute_link'),
                $subSelect->expr->lAnd(
                    [
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn('objectattribute_version', 'eztags_attribute_link'),
                            $this->dbHandler->quoteColumn('current_version', 'ezcontentobject')
                        ),
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn('object_id', 'eztags_attribute_link'),
                            $this->dbHandler->quoteColumn('id', 'ezcontentobject')
                        ),
                    ]
                )
            )->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn('keyword_id', 'eztags_attribute_link'),
                    $criterion->value
                )
            );

        $fieldDefinitionIds = $this->getSearchableFields($criterion->target);
        if ($fieldDefinitionIds !== null) {
            $subSelect->innerJoin(
                $this->dbHandler->quoteTable('ezcontentobject_attribute'),
                $subSelect->expr->lAnd(
                    [
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn('id', 'ezcontentobject_attribute'),
                            $this->dbHandler->quoteColumn('objectattribute_id', 'eztags_attribute_link')
                        ),
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn('version', 'ezcontentobject_attribute'),
                            $this->dbHandler->quoteColumn('objectattribute_version', 'eztags_attribute_link')
                        ),
                    ]
                )
            );

            $subSelect->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn('contentclassattribute_id', 'ezcontentobject_attribute'),
                    $fieldDefinitionIds
                )
            );
        }

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subSelect
        );
    }
}
