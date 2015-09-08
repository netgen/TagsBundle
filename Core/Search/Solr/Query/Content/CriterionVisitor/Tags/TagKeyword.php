<?php

namespace Netgen\TagsBundle\Core\Search\Solr\Query\Content\CriterionVisitor\Tags;

use eZ\Publish\Core\Search\Solr\Query\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Netgen\TagsBundle\Core\Search\Solr\Query\Content\CriterionVisitor\Tags;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagKeyword as APITagKeyword;

class TagKeyword extends Tags
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof APITagKeyword;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Solr\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $criterion->value = (array)$criterion->value;

        $fieldNames = $this->fieldNameResolver->getFieldNames(
            $criterion,
            $criterion->target,
            $this->fieldTypeIdentifier,
            $this->fieldName
        );

        if (empty($fieldNames)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$criterion->target}'."
            );
        }

        $queries = array();
        foreach ($criterion->value as $value) {
            foreach ($fieldNames as $name) {
                if ($criterion->operator === Operator::LIKE) {
                    $queries[] = "{$name}:{$value}";
                } else {
                    $queries[] = "\"{$name}:{$value}\"";
                }
            }
        }

        return '(' . implode(' OR ', $queries) . ')';
    }
}
