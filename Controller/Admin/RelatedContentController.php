<?php

namespace Netgen\TagsBundle\Controller\Admin;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Pagerfanta\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;

class RelatedContentController extends Controller
{
    /**
     * @var \Pagerfanta\Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * Constructor.
     *
     * @param \Pagerfanta\Adapter\AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Rendering a view which shows related content of tag.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function relatedContentAction(Request $request, Tag $tag)
    {
        $this->denyAccessUnlessGranted('ez:tags:read');

        $currentPage = (int)$request->query->get('page');
        $configResolver = $this->getConfigResolver();

        $pager = $this->createPager(
            $this->adapter,
            $currentPage,
            $configResolver->getParameter('admin.related_content_limit', 'eztags'),
            $tag
        );

        return $this->render(
            'NetgenTagsBundle:admin/tag:related_content.html.twig',
            array(
                'tag' => $tag,
                'related_content' => $pager,
            )
        );
    }
}
