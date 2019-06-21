<?php

namespace Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\Tags;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagKeyword as TagKeywordCriterion;
use Netgen\TagsBundle\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\Tags;

class TagKeyword extends Tags
{
    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof TagKeywordCriterion;
    }

    public function handle(CriteriaConverter $converter, SelectQuery $query, Criterion $criterion, ?array $fieldFilters = null): string
    {
        /** @var \Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\Value\TagKeywordValue $valueData */
        $valueData = $criterion->valueData;

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
            )->innerJoin(
                $this->dbHandler->quoteTable('eztags'),
                $subSelect->expr->eq(
                    $this->dbHandler->quoteColumn('keyword_id', 'eztags_attribute_link'),
                    $this->dbHandler->quoteColumn('id', 'eztags')
                )
            )->leftJoin(
                $this->dbHandler->quoteTable('eztags_keyword'),
                $subSelect->expr->lAnd(
                    $subSelect->expr->eq(
                        $this->dbHandler->quoteColumn('id', 'eztags'),
                        $this->dbHandler->quoteColumn('keyword_id', 'eztags_keyword')
                    ),
                    $subSelect->expr->eq(
                        $this->dbHandler->quoteColumn('status', 'eztags_keyword'),
                        $subSelect->bindValue(1, null, \PDO::PARAM_INT)
                    )
                )
            );

        if ($valueData !== null && count($valueData->languages) > 0) {
            if ($valueData->useAlwaysAvailable) {
                $subSelect->where(
                    $subSelect->expr->lOr(
                        $subSelect->expr->in(
                            $this->dbHandler->quoteColumn('locale', 'eztags_keyword'),
                            $valueData->languages
                        ),
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn('main_language_id', 'eztags'),
                            $subSelect->expr->bitAnd(
                                $this->dbHandler->quoteColumn('language_id', 'eztags_keyword'),
                                -2 // -2 == PHP_INT_MAX << 1
                            )
                        )
                    )
                );
            } else {
                $subSelect->where(
                    $subSelect->expr->in(
                        $this->dbHandler->quoteColumn('locale', 'eztags_keyword'),
                        $valueData->languages
                    )
                );
            }
        }

        if ($criterion->operator === Criterion\Operator::LIKE) {
            $subSelect->where(
                $subSelect->expr->like(
                    $this->dbHandler->quoteColumn('keyword', 'eztags_keyword'),
                    $subSelect->bindValue($criterion->value[0])
                )
            );
        } else {
            $subSelect->where(
                $subSelect->expr->in(
                    $this->dbHandler->quoteColumn('keyword', 'eztags_keyword'),
                    $criterion->value
                )
            );
        }

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
