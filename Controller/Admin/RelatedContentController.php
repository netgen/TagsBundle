<?php

namespace Netgen\TagsBundle\Controller\Admin;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\Pagination\Pagerfanta\TagAdapterInterface;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

class RelatedContentController extends Controller
{
    /**
     * @var \Pagerfanta\Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * @var int
     */
    protected $pagerLimit = 25;

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
     * Sets the pager limit.
     *
     * @param int $pagerLimit
     */
    public function setPagerLimit($pagerLimit)
    {
        $this->pagerLimit = (int)$pagerLimit;
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
        if ($this->adapter instanceof TagAdapterInterface) {
            $this->adapter->setTag($tag);
        }

        $pager = new Pagerfanta($this->adapter);

        $pager->setNormalizeOutOfRangePages(true);

        $page = (int)$request->query->get('page');
        $pager->setMaxPerPage($this->pagerLimit > 0 ? $this->pagerLimit : 10);
        $pager->setCurrentPage($page > 0 ? $page : 1);

        return $this->render(
            'NetgenTagsBundle:admin/tag:related_content.html.twig',
            array(
                'tag' => $tag,
                'related_content' => $pager,
            )
        );
    }
}
