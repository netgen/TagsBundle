<?php

namespace Netgen\TagsBundle\Controller;

use Netgen\TagsBundle\View\TagView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use eZ\Bundle\EzPublishCoreBundle\Controller;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Netgen\TagsBundle\Core\Pagination\Pagerfanta\TagAdapterInterface;

class TagViewController extends Controller
{
    /**
     * Action for rendering a tag view.
     *
     * @param \Netgen\TagsBundle\View\TagView $view
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(TagView $view)
    {
        return $view;
    }
}
