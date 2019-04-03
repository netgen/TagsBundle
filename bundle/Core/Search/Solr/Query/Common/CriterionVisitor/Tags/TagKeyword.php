<?php

namespace Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagKeyword as APITagKeyword;
use Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor\Tags;

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
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor $subVisitor
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $criterion->value = (array) $criterion->value;
        $searchFields = $this->getSearchFields($criterion);
        $isLikeOperator = $criterion->operator === Operator::LIKE;

        if (empty($searchFields)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$criterion->target}'."
            );
        }

        $queries = [];
        foreach ($searchFields as $name => $fieldType) {
            foreach ($criterion->value as $value) {
                $preparedValue = $this->escapeQuote(
                    $this->toString(
                        $this->mapSearchFieldValue($value, $fieldType)
                    ),
                    true
                );

                if ($isLikeOperator) {
                    $queries[] = '{!prefix f=' . $name . ' v="' . $preparedValue . '"}';
                } else {
                    $queries[] = $name . ':"' . $preparedValue . '"';
                }
            }
        }

        return '(' . implode(' OR ', $queries) . ')';
    }
}
