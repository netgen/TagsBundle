<?php

namespace Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * A criterion that matches content based on tag ID that is located in one of the fields.
 *
 * Supported operators:
 * - IN: matches against a list of tag IDs (with OR operator)
 * - EQ: matches against one tag ID
 */
class TagId extends Criterion implements CriterionInterface
{
    /**
     * Creates a new TagId criterion.
     *
     * @param int|int[] $value One or more tag IDs that must be matched
     * @param string $target Field definition identifier to use
     *
     * @throws \InvalidArgumentException if a non numeric id is given
     * @throws \InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct($value, $target = null)
    {
        parent::__construct($target, null, $value);
    }

    public function getSpecifications()
    {
        return array(
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
        );
    }

    public static function createFromQueryBuilder($target, $operator, $value)
    {
        return new self($value);
    }
}
