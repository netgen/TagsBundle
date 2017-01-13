<?php

namespace Netgen\TagsBundle\Controller\Admin;

use eZ\Bundle\EzPublishCoreBundle\Controller as BaseController;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class Controller extends BaseController
{
    /**
     * Redirects to tag page or dashboard if tag is not provided.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToTagOrDashboard(Tag $tag = null)
    {
        if (!$tag instanceof Tag) {
            return $this->redirectToRoute('netgen_tags_admin_dashboard_index');
        }

        return $this->redirectToRoute(
            'netgen_tags_admin_tag_show',
            array(
                'tagId' => $tag->id,
            )
        );
    }
}
