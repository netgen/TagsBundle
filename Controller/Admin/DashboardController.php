<?php

namespace Netgen\TagsBundle\Controller\Admin;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use Netgen\TagsBundle\API\Repository\TagsService;

class DashboardController extends Controller
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * DashboardController constructor.
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    public function indexAction()
    {
        $parentTag = $this->tagsService->loadTag(4);
        $tags = $this->tagsService->loadTagChildren(null, 0, 10);

        return $this->render('@NetgenTags/admin/dashboard/index.html.twig', array(
            'latestTags' => $tags,
        ));
    }
}
