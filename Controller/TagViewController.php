<?php

namespace Netgen\TagsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use eZ\Bundle\EzPublishCoreBundle\Controller;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter;
use Pagerfanta\Pagerfanta;

class TagViewController extends Controller
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * Constructor
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct( TagsService $tagsService )
    {
        $this->tagsService = $tagsService;
    }

    /**
     * Action for rendering a tag view by using tag ID
     *
     * @param mixed $tagId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewTagById( $tagId )
    {
        $tag = $this->tagsService->loadTag( $tagId );
        return $this->renderTag( $tag );
    }

    /**
     * Action for rendering a tag view by using tag URL
     *
     * @param string $tagUrl
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewTagByUrl( $tagUrl )
    {
        $tag = $this->tagsService->loadTagByUrl( $tagUrl );
        return $this->renderTag( $tag );
    }

    /**
     * Renders the tag
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderTag( Tag $tag )
    {
        $pager = new Pagerfanta(
            new RelatedContentAdapter( $tag, $this->tagsService )
        );

        $pager->setMaxPerPage( $this->getConfigResolver()->getParameter( 'tag_view.related_content_list.limit', 'eztags' ) );
        $pager->setCurrentPage( $this->getRequest()->get( 'page', 1 ) );

        return $this->render(
            'NetgenTagsBundle:tag:view.html.twig',
            array(
                'tag' => $tag,
                'pager' => $pager
            )
        );
    }
}
