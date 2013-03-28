<?php

namespace EzSystems\TagsBundle\Core\Repository;

use eZ\Publish\API\Repository\Repository;
use EzSystems\TagsBundle\API\Repository\TagsService as TagsServiceInterface;
use EzSystems\TagsBundle\SPI\Persistence\Tags\Handler;

class TagsService implements TagsServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \EzSystems\TagsBundle\SPI\Persistence\Tags\Handler
     */
    protected $tagsHandler;

    /**
     * Constructor
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \EzSystems\TagsBundle\SPI\Persistence\Tags\Handler $tagsHandler
     */
    public function __construct( Repository $repository, Handler $tagsHandler )
    {
        $this->repository = $repository;
        $this->tagsHandler = $tagsHandler;
    }
}
