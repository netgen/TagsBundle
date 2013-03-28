<?php

namespace EzSystems\TagsBundle\Core\Persistence\Legacy\Tags;

use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use EzSystems\TagsBundle\SPI\Persistence\Tags\Handler as BaseTagsHandler;
use EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;
use EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Mapper;

class Handler implements BaseTagsHandler
{
    /**
     * @var \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway
     */
    protected $gateway;

    /**
     * @var \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Mapper
     */
    protected $mapper;

    /**
     * @param \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway $gateway
     * @param \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Mapper $mapper
     */
    public function __construct( Gateway $gateway, Mapper $mapper )
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
    }
}
