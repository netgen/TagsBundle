<?php

namespace Netgen\TagsBundle\Tests\Core\Base\Container\ApiLoader;

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

    public function __construct($repositoryClass, FieldTypeCollectionFactory $fieldTypeCollectionFactory)
    {
        $this->repositoryClass = $repositoryClass;
        $this->fieldTypeCollectionFactory = $fieldTypeCollectionFactory;
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
                'role' => array(
                    'limitationMap' => array('tags' => array('add' => array('Tag' => true))),
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
