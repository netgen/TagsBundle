<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Search\Elasticsearch\QueryDSL;

use Ibexa\Elasticsearch\ElasticSearch\QueryDSL\Query;

final class PrefixQuery implements Query
{
    private ?string $field;

    private ?string $value;

    public function __construct(?string $field = null, ?string $value = null)
    {
        $this->field = $field;
        $this->value = $value;
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
