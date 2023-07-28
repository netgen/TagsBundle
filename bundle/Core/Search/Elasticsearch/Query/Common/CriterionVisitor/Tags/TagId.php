<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Search\Elasticsearch\Query\Common\CriterionVisitor\Tags;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Elasticsearch\Query\CriterionVisitor;
use Ibexa\Contracts\Elasticsearch\Query\LanguageFilter;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Elasticsearch\ElasticSearch\QueryDSL\BoolQuery;
use Ibexa\Elasticsearch\ElasticSearch\QueryDSL\TermQuery;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId as APITagId;
use Netgen\TagsBundle\Core\Search\Elasticsearch\Query\Common\CriterionVisitor\Tags;

use function count;

final class TagId extends Tags
{
    public function supports(Criterion $criterion, LanguageFilter $languageFilter): bool
    {
        return $criterion instanceof APITagId;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, LanguageFilter $languageFilter): array
    {
        $criterion->value = (array) $criterion->value;
        $searchFields = $this->getSearchFields($criterion);

        if (count($searchFields) === 0) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$criterion->target}'.",
            );
        }

        $query = new BoolQuery();
        foreach ($searchFields as $name => $fieldType) {
            foreach ($criterion->value as $value) {
                $query->addShould(new TermQuery($name, $value));
            }
        }

        return $query->toArray();
    }
}
