<?php

namespace Netgen\TagsBundle\Controller\Admin;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter;
use Netgen\TagsBundle\Core\Search\RelatedContent\SortClauseMapper;
use Netgen\TagsBundle\Form\Type\RelatedContentFilterType;
use Pagerfanta\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;

class RelatedContentController extends Controller
{
    /**
     * @var \Pagerfanta\Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @var \Netgen\TagsBundle\Core\Search\RelatedContent\SortClauseMapper
     */
    protected $sortClauseMapper;

    /**
     * Constructor.
     *
     * @param \Pagerfanta\Adapter\AdapterInterface $adapter
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \Netgen\TagsBundle\Core\Search\RelatedContent\SortClauseMapper $sortClauseMapper
     */
    public function __construct(AdapterInterface $adapter, ContentTypeService $contentTypeService, SortClauseMapper $sortClauseMapper)
    {
        $this->adapter = $adapter;
        $this->contentTypeService = $contentTypeService;
        $this->sortClauseMapper = $sortClauseMapper;
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

        $currentPage = (int) $request->query->get('page');
        $configResolver = $this->getConfigResolver();
        $filterApplied = false;

        $form = $this->createForm(
            RelatedContentFilterType::class,
            null,
            [
                'tag' => $tag,
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contentTypeFilter = $form->get('content_types')->getData();
            $sortOption = $form->get('sort')->getData();

            if ($this->adapter instanceof RelatedContentAdapter) {
                if (!empty($contentTypeFilter)) {
                    $additionalCriteria = [
                        new ContentTypeIdentifier($contentTypeFilter),
                    ];

                    $this->adapter->setAdditionalCriteria($additionalCriteria);
                }

                $sortClauses = $this->sortClauseMapper->mapSortClauses([$sortOption]);
                $this->adapter->setSortClauses($sortClauses);

                $filterApplied = true;
            }
        }

        $pager = $this->createPager(
            $this->adapter,
            $currentPage,
            $configResolver->getParameter('admin.related_content_limit', 'eztags'),
            $tag
        );

        return $this->render(
            '@NetgenTags/admin/tag/related_content.html.twig',
            [
                'tag' => $tag,
                'related_content' => $pager,
                'filter_form' => $form->createView(),
                'filter_applied' => $filterApplied,
            ]
        );
    }
}
