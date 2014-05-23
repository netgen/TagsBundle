<?php

namespace Netgen\TagsBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use Pagerfanta\Pagerfanta;

use Exception;

class TagViewController extends Controller
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
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
     * Action for rendering a tag view by using tag URL (slug)
     *
     * @param string $slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewTagBySlug( $slug )
    {
        $tag = $this->tagsService->loadTagByUrl( $slug );
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
        $response = new Response();
        try
        {
            $relatedContent = $this->tagsService->getRelatedContent( $tag );

            return $this->render(
                'NetgenTagsBundle:tag:view.html.twig',
                array(
                    'tag' => $tag,
                    'relatedContent' => $relatedContent
                ),
                $response
            );
        }
        catch ( Exception $e )
        {
            $this->get( 'logger' )->error( "An exception has been raised when viewing tag: {$e->getMessage()}" );
            return $response;
        }
    }
}
