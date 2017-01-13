<?php

namespace Netgen\TagsBundle\Controller\Admin;

use eZ\Bundle\EzPublishCoreBundle\Controller as BaseController;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

abstract class Controller extends BaseController
{
    /**
     * Ensures that only authenticated users can access to controller.
     * It is not needed to call this method from actions
     * as it's already called from base controller service.
     *
     * @see eztags.admin.controller.base service definition
     */
    public function performAccessChecks()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    }

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
