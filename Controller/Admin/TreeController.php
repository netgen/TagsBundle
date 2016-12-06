<?php

namespace Netgen\TagsBundle\Controller\Admin;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;

class TreeController extends Controller
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * TreeController constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * Returns JSON string containing all children tags for given tag.
     * It is called in AJAX request from jsTree Javascript plugin to render tree with tags.
     * It supports lazy loading; when a tag is clicked in a tree, it calls this method to fetch it's children.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag|null $tag
     * @param bool $isRoot
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getChildrenAction(Tag $tag = null, $isRoot = false)
    {
        $childrenTags = $this->tagsService->loadTagChildren($tag);

        $result = array();

        if ((bool) $isRoot) {
            if ($tag === null) {
                $result = array(
                    array(
                        'id' => '0',
                        'parent' => '#',
                        'text' => 'Top level tags',
                        'children' => true,
                        'state' => array(
                            'opened' => true,
                        ),
                        'a_attr' => array(
                            'href' => $this->generateUrl(
                                'netgen_tags_admin_dashboard_index'
                            ),
                        ),
                    ),
                );
            } else {
                $result = $this->getTagTreeData($tag, $isRoot);
            }
        } else {
            foreach ($childrenTags as $tag) {
                $result[] = $this->getTagTreeData($tag, $isRoot);
            }
        }

        return (new JsonResponse())->setData($result);
    }

    protected function getTagTreeData(Tag $tag, $isRoot = false)
    {
        $synonymCount = $tag === null ?
            0 :
            $this->tagsService->getTagSynonymCount($tag);

        return array(
            'id' => $tag->id,
            'parent' => $isRoot ? '#' : $tag->parentTagId,
            'text' => $synonymCount > 0 ? $tag->keyword . ' (+' . $synonymCount . ')' : $tag->keyword,
            'children' => $this->tagsService->getTagChildrenCount($tag) > 0,
            'a_attr' => array(
                'href' => $this->generateUrl(
                    'netgen_tags_admin_tag_show',
                    array(
                        'tagId' => $tag->id,
                    )
                ),
            ),
        );
    }
}
