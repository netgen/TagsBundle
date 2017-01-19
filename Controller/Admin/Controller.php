<?php

namespace Netgen\TagsBundle\Controller\Admin;

use eZ\Bundle\EzPublishCoreBundle\Controller as BaseController;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\Pagination\Pagerfanta\TagAdapterInterface;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;

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
            return $this->redirectToRoute('netgen_tags_admin_root');
        }

        return $this->redirectToRoute(
            'netgen_tags_admin_tag_show',
            array(
                'tagId' => $tag->id,
            )
        );
    }

    /**
     * Adds a flash message with specified parameters.
     *
     * @param string $messageType
     * @param string $message
     * @param array $parameters
     */
    protected function addFlashMessage($messageType, $message, array $parameters = array())
    {
        $this->addFlash(
            'tags.' . $messageType,
            $this->get('translator')->trans(
                $messageType . '.' . $message,
                $parameters,
                'eztags_admin_flash'
            )
        );
    }

    /**
     * Creates a pager for use with various pages.
     *
     * @param \Pagerfanta\Adapter\AdapterInterface $adapter
     * @param int $currentPage
     * @param int $maxPerPage
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Pagerfanta\Pagerfanta
     */
    protected function createPager(AdapterInterface $adapter, $currentPage, $maxPerPage, Tag $tag = null)
    {
        if ($adapter instanceof TagAdapterInterface && $tag instanceof Tag) {
            $adapter->setTag($tag);
        }

        $pager = new Pagerfanta($adapter);

        $pager->setNormalizeOutOfRangePages(true);
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($currentPage > 0 ? $currentPage : 1);

        return $pager;
    }
}
