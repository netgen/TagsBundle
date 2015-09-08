<?php

namespace Netgen\TagsBundle\Core\Search\Solr\Query\Content\CriterionVisitor;

use eZ\Publish\Core\Search\Solr\Query\CriterionVisitor;
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
     * @param string $fieldTypeIdentifier
     * @param string $fieldName
     */
    public function __construct(FieldNameResolver $fieldNameResolver, $fieldTypeIdentifier, $fieldName)
    {
        $this->fieldNameResolver = $fieldNameResolver;
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
        $this->fieldName = $fieldName;
    }
}
