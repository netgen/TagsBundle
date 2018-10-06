<?php

namespace Netgen\TagsBundle\Tests\Core\Base\Container\ApiLoader;

use eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeCollectionFactory;
use eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeNameableCollectionFactory;
use eZ\Publish\Core\Base\Container\ApiLoader\RepositoryFactory as BaseRepositoryFactory;

class RepositoryFactory extends BaseRepositoryFactory
{
    public function __construct(
        $repositoryClass,
        FieldTypeCollectionFactory $fieldTypeCollectionFactory,
        FieldTypeNameableCollectionFactory $fieldTypeNameableCollectionFactory,
        array $policyMap
    ) {
        $policyMap['tags'] = array(
            'add' => array('Tag' => true),
            'read' => array(),
            'editsynonym' => array(),
            'addsynonym' => array(),
            'makesynonym' => array(),
            'merge' => array(),
            'edit' => array(),
            'delete' => array(),
        );

        parent::__construct(
            $repositoryClass,
            $fieldTypeCollectionFactory,
            $fieldTypeNameableCollectionFactory,
            $policyMap
        );
    }
}
