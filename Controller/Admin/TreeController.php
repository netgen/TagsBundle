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
                        'opened' => true,
                    ),
                );
            } else {
                $synonymCount = $tag === null ?
                    0 :
                    $this->tagsService->getTagSynonymCount($tag);

                $result = array(
                    array(
                        'id' => $tag->id,
                        'parent' => '#',
                        'text' => $synonymCount > 0 ? $tag->keyword . ' (+' . $synonymCount . ')' : $tag->keyword,
                        'children' => $this->tagsService->getTagChildrenCount($tag) > 0,
                    ),
                );
            }
        } else {
            foreach ($childrenTags as $tag) {
                $synonymCount = $tag === null ?
                    0 :
                    $this->tagsService->getTagSynonymCount($tag);

                $result[] = array(
                    'id' => $tag->id,
                    'parent' => $tag->parentTagId,
                    'text' => $synonymCount > 0 ? $tag->keyword . ' (+' . $synonymCount . ')' : $tag->keyword,
                    'children' => $this->tagsService->getTagChildrenCount($tag) > 0,
                );
            }
        }

        return (new JsonResponse())->setData($result);
    }
}
