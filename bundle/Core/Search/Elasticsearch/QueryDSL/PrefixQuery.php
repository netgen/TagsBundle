<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Search\Elasticsearch\QueryDSL;

use Ibexa\Elasticsearch\ElasticSearch\QueryDSL\Query;

final class PrefixQuery implements Query
{
    public function __construct(
        private ?string $field = null,
        private ?string $value = null,
    ) {
    }

    public function withField(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function withValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'prefix' => [
                $this->field => $this->value,
            ],
        ];
    }
}
