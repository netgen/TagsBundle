<?php

namespace Netgen\TagsBundle\Core\Search\Solr\Query\Common\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use eZ\Publish\Core\Search\Common\FieldValueMapper;
use eZ\Publish\SPI\Persistence\Content\Type\Handler;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field;

abstract class Tags extends Field
{
    /**
     * For tag-queries which aren't field-specific.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    private $contentTypeHandler;

    /**
     * Identifier of the field type that criterion can handle.
     *
     * @var string
     */
    private $fieldTypeIdentifier;

    /**
     * Name of the field type's indexed field that criterion can handle.
     *
     * @var string
     */
    private $fieldName;

    /**
     * Create from FieldNameResolver, FieldType identifier and field name.
     *
     * @param \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver
     * @param \eZ\Publish\Core\Search\Common\FieldValueMapper $fieldValueMapper
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param string $fieldTypeIdentifier
     * @param string $fieldName
     */
    public function __construct(
        FieldNameResolver $fieldNameResolver,
        FieldValueMapper $fieldValueMapper,
        Handler $contentTypeHandler,
        $fieldTypeIdentifier,
        $fieldName
    ) {
        parent::__construct($fieldNameResolver, $fieldValueMapper);

        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
        $this->fieldName = $fieldName;
    }

    /**
     * Get array of search fields.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \eZ\Publish\SPI\Search\FieldType[] Array of field types indexed by name
     */
    protected function getSearchFields(Criterion $criterion)
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

                $targetFieldTypes = array_merge($targetFieldTypes, $fieldTypes);
            }
        }

        return $targetFieldTypes;
    }
}
