<?php

namespace EzSystems\TagsBundle\SignalSlot\Repository;

use EzSystems\TagsBundle\API\Repository\TagsService as TagsServiceInterface;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;

class TagsService implements TagsServiceInterface
{
    /**
     * @var \EzSystems\TagsBundle\API\Repository\TagsService
     */
    protected $service;

    /**
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * @param \EzSystems\TagsBundle\API\Repository\TagsService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct( TagsServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service = $service;
        $this->signalDispatcher = $signalDispatcher;
    }
}
