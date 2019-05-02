<?php

namespace Netgen\TagsBundle\Controller\Admin;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Search\Facet\ContentTypeFacet;
use Netgen\Bundle\EnhancedSelectionBundle\Form\Type\FieldType\OptionType;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter;
use Netgen\TagsBundle\Form\Type\ContentTypeFilterType;
use Pagerfanta\Adapter\AdapterInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
     * Constructor.
     *
     * @param \Pagerfanta\Adapter\AdapterInterface $adapter
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(AdapterInterface $adapter, ContentTypeService $contentTypeService)
    {
        $this->adapter = $adapter;
        $this->contentTypeService = $contentTypeService;
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
            ContentTypeFilterType::class,
            null,
            array(
                'tag' => $tag,
            )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contentTypeFilter = $form->get('content_types')->getData();

            if ($this->adapter instanceof RelatedContentAdapter && !empty($contentTypeFilter)) {
                $additionalCriteria = [
                    new ContentTypeIdentifier($contentTypeFilter)
                ];

                $this->adapter->setAdditionalCriteria($additionalCriteria);
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
