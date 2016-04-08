<?php

namespace Netgen\TagsBundle\Core\Search\Solr\Query\Content\CriterionVisitor;

use eZ\Publish\SPI\Persistence\Content\Type\Handler;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

abstract class Tags extends CriterionVisitor
{
    /**
     * Field map.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldNameResolver
     */
    protected $fieldNameResolver;

    /**
     * For tag-queries which aren't field-specific.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Identifier of the field type that criterion can handle.
     *
     * @var string
     */
    protected $fieldTypeIdentifier;

    /**
     * Name of the field type's indexed field that criterion can handle.
     *
     * @var string
     */
    protected $fieldName;

    /**
     * Create from FieldNameResolver, FieldType identifier and field name.
     *
     * @param \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param string $fieldTypeIdentifier
     * @param string $fieldName
     */
    public function __construct(FieldNameResolver $fieldNameResolver, Handler $contentTypeHandler, $fieldTypeIdentifier, $fieldName)
    {
        $this->fieldNameResolver = $fieldNameResolver;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
        $this->fieldName = $fieldName;
    }

    /**
     * Resolves the targeted fields for this criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return array
     */
    protected function getTargetFieldNames(Criterion $criterion)
    {
        if ($criterion->target != null) {
            return $this->fieldNameResolver->getFieldNames(
                $criterion,
                $criterion->target,
                $this->fieldTypeIdentifier,
                $this->fieldName
            );
        }

        $targetFieldNames = array();
        foreach ($this->contentTypeHandler->getSearchableFieldMap() as $fieldDefinitions) {
            foreach ($fieldDefinitions as $fieldIdentifier => $fieldDefinition) {
                if (!isset($fieldDefinition['field_type_identifier'])) {
                    continue;
                }

                if ($fieldDefinition['field_type_identifier'] != $this->fieldTypeIdentifier) {
                    continue;
                }

                $solrFieldNames = $this->fieldNameResolver->getFieldNames(
                    $criterion,
                    $fieldIdentifier,
                    $this->fieldTypeIdentifier,
                    $this->fieldName
                );

                $targetFieldNames = array_merge($targetFieldNames, $solrFieldNames);
            }
        }

        return array_values(array_unique($targetFieldNames));
    }
}
