<?php

namespace Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value;

/**
 * A criterion that matches content based on tag keyword that is located in one of the fields.
 *
 * Supported operators:
 * - IN: matches against a list of tag keywords (with OR operator)
 * - EQ: matches against one tag keyword
 * - LIKE: matches against a part of tag keyword
 */
class TagKeyword extends Criterion
{
    /**
     * Creates a new TagKeyword criterion.
     *
     * @param string $operator
     * @param string|string[] $value One or more tag keywords that must be matched
     * @param string $target Field definition identifier to use
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value $valueData
     *
     * @throws \InvalidArgumentException if a non string parameter is given
     * @throws \InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct($operator, $value, $target = null, Value $valueData = null)
    {
        parent::__construct($target, $operator, $value, $valueData);
    }

    public function getSpecifications()
    {
        return [
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::LIKE,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_STRING
            ),
        ];
    }
}
