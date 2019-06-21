<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Core\Base\Container\ApiLoader;

use eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeCollectionFactory;
use eZ\Publish\Core\Base\Container\ApiLoader\RepositoryFactory as BaseRepositoryFactory;

final class RepositoryFactory extends BaseRepositoryFactory
{
    public function __construct(
        $repositoryClass,
        FieldTypeCollectionFactory $fieldTypeCollectionFactory,
        array $policyMap
    ) {
        $policyMap['tags'] = [
            'add' => ['Tag' => true],
            'read' => [],
            'editsynonym' => [],
            'addsynonym' => [],
            'makesynonym' => [],
            'merge' => [],
            'edit' => [],
            'delete' => [],
        ];

        parent::__construct(
            $repositoryClass,
            $fieldTypeCollectionFactory,
            $policyMap
        );
    }
}
