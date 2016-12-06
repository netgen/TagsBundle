<?php

namespace Netgen\TagsBundle\Tests\Core\Base\Container\ApiLoader;

use eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeNameableCollectionFactory;
use eZ\Publish\Core\Base\Container\ApiLoader\RepositoryFactory as BaseRepositoryFactory;
use eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeCollectionFactory;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandler;

class RepositoryFactory extends BaseRepositoryFactory
{
    /**
     * @var string
     */
    private $repositoryClass;

    /**
     * Collection of fieldTypes, lazy loaded via a closure.
     *
     * @var \eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeNameableCollectionFactory
     */
    protected $fieldTypeNameableCollectionFactory;

    public function __construct(
        $repositoryClass,
        FieldTypeCollectionFactory $fieldTypeCollectionFactory,
        FieldTypeNameableCollectionFactory $fieldTypeNameableCollectionFactory
    ) {
        $this->repositoryClass = $repositoryClass;
        $this->fieldTypeCollectionFactory = $fieldTypeCollectionFactory;
        $this->fieldTypeNameableCollectionFactory = $fieldTypeNameableCollectionFactory;
    }

    /**
     * Builds the main repository, heart of eZ Publish API.
     *
     * This always returns the true inner Repository, please depend on ezpublish.api.repository and not this method
     * directly to make sure you get an instance wrapped inside Signal / Cache / * functionality.
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function buildRepository(PersistenceHandler $persistenceHandler, SearchHandler $searchHandler)
    {
        $repository = new $this->repositoryClass(
            $persistenceHandler,
            $searchHandler,
            array(
                'fieldType' => $this->fieldTypeCollectionFactory->getFieldTypes(),
                'nameableFieldTypes' => $this->fieldTypeNameableCollectionFactory->getNameableFieldTypes(),
                'role' => array(
                    'policyMap' => array('tags' => array('add' => array('Tag' => true))),
                    'limitationTypes' => $this->roleLimitations,
                ),
                'languages' => $this->container->getParameter('languages'),
            )
        );

        /** @var \eZ\Publish\API\Repository\Repository $repository */
        $anonymousUser = $repository->getUserService()->loadUser(
            $this->container->getParameter('anonymous_user_id')
        );
        $repository->setCurrentUser($anonymousUser);

        return $repository;
    }
}
