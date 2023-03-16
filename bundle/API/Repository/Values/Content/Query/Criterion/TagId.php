<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator\Specifications;

/**
 * A criterion that matches content based on tag ID that is located in one of the fields.
 *
 * Supported operators:
 * - IN: matches against a list of tag IDs (with OR operator)
 * - EQ: matches against one tag ID
 */
final class TagId extends Criterion
{
    /**
     * @param int|int[] $value One or more tag IDs that must be matched
     * @param string|null $target Field definition identifier to use
     */
    public function __construct(int|array $value, ?string $target = null)
    {
        parent::__construct($target, null, $value);
    }

    public function getSpecifications(): array
    {
        return [
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING,
            ),
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING,
            ),
        ];
    }
}
