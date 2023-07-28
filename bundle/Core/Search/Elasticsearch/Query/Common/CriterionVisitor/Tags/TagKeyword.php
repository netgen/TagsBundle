<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Search\Elasticsearch\Query\Common\CriterionVisitor\Tags;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Elasticsearch\Query\CriterionVisitor;
use Ibexa\Contracts\Elasticsearch\Query\LanguageFilter;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Elasticsearch\ElasticSearch\QueryDSL\BoolQuery;
use Ibexa\Elasticsearch\ElasticSearch\QueryDSL\TermQuery;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagKeyword as APITagKeyword;
use Netgen\TagsBundle\Core\Search\Elasticsearch\Query\Common\CriterionVisitor\Tags;
use Netgen\TagsBundle\Core\Search\Elasticsearch\QueryDSL\PrefixQuery;

use function count;

final class TagKeyword extends Tags
{
    public function supports(Criterion $criterion, LanguageFilter $languageFilter): bool
    {
        return $criterion instanceof APITagKeyword;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, LanguageFilter $languageFilter): array
    {
        $criterion->value = (array) $criterion->value;
        $searchFields = $this->getSearchFields($criterion);
        $isLikeOperator = $criterion->operator === Operator::LIKE;

        if (count($searchFields) === 0) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$criterion->target}'.",
            );
        }

        $query = new BoolQuery();
        foreach ($searchFields as $name => $fieldType) {
            /**
             * @var string $value
             */
            foreach ($criterion->value as $value) {
                if ($isLikeOperator) {
                    $query->addShould(new PrefixQuery($name, $value));
                } else {
                    $query->addShould(new TermQuery($name, $value));
                }
            }
        }

        return $query->toArray();
    }
}
