<?php

namespace Netgen\TagsBundle\Controller;

use Netgen\TagsBundle\View\TagView;
use eZ\Bundle\EzPublishCoreBundle\Controller;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

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
