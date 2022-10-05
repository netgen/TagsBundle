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
     * For tag-queries which aren't field-specific.
     */
    private Handler $contentTypeHandler;

    /**
     * Identifier of the field type that criterion can handle.
     */
    private string $fieldTypeIdentifier;

    /**
     * Name of the field type's indexed field that criterion can handle.
     */
    private string $fieldName;

    public function __construct(
        FieldNameResolver $fieldNameResolver,
        FieldValueMapper $fieldValueMapper,
        Handler $contentTypeHandler,
        string $fieldTypeIdentifier,
        string $fieldName
    ) {
        parent::__construct($fieldNameResolver, $fieldValueMapper);

        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
        $this->fieldName = $fieldName;
    }

    protected function getSearchFields(Criterion $criterion): array
    {
        if ($criterion->target !== null) {
            return $this->fieldNameResolver->getFieldTypes(
                $criterion,
                $criterion->target,
                $this->fieldTypeIdentifier,
                $this->fieldName
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
                    $this->fieldName
                );

                $targetFieldTypes[] = $fieldTypes;
            }
        }

        return array_merge(...$targetFieldTypes);
    }
}
