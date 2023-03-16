<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Persistence\Content\Type\Handler;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Core\Search\Common\FieldNameResolver;
use Ibexa\Core\Search\Common\FieldValueMapper;
use Ibexa\Solr\Query\Common\CriterionVisitor\Field;

use function array_merge;

abstract class Tags extends Field
{
    /**
     * @param \Ibexa\Contracts\Core\Persistence\Content\Type\Handler $contentTypeHandler for tag-queries which aren't field-specific
     * @param string $fieldTypeIdentifier identifier of the field type that criterion can handle
     * @param string $fieldName name of the field type's indexed field that criterion can handle
     */
    public function __construct(
        FieldNameResolver $fieldNameResolver,
        FieldValueMapper $fieldValueMapper,
        private Handler $contentTypeHandler,
        private string $fieldTypeIdentifier,
        private string $fieldName,
    ) {
        parent::__construct($fieldNameResolver, $fieldValueMapper);
    }

    protected function getSearchFields(Criterion $criterion): array
    {
        if ($criterion->target !== null) {
            return $this->fieldNameResolver->getFieldTypes(
                $criterion,
                $criterion->target,
                $this->fieldTypeIdentifier,
                $this->fieldName,
            );
        }

        $targetFieldTypes = [];
        foreach ($this->contentTypeHandler->getSearchableFieldMap() as $fieldDefinitions) {
            foreach ($fieldDefinitions as $fieldIdentifier => $fieldDefinition) {
                if (!isset($fieldDefinition['field_type_identifier'])) {
                    continue;
                }

                if ($fieldDefinition['field_type_identifier'] !== $this->fieldTypeIdentifier) {
                    continue;
                }

                $fieldTypes = $this->fieldNameResolver->getFieldTypes(
                    $criterion,
                    $fieldIdentifier,
                    $this->fieldTypeIdentifier,
                    $this->fieldName,
                );

                $targetFieldTypes[] = $fieldTypes;
            }
        }

        return array_merge(...$targetFieldTypes);
    }
}
