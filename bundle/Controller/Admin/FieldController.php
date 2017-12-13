<?php

namespace Netgen\TagsBundle\Controller\Admin;

use Netgen\TagsBundle\API\Repository\TagsService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FieldController extends Controller
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var array
     */
    protected $languages = array();

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService$translationHelper
     */
    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * Sets the list of available languages to controller.
     *
     * @param array $languages
     */
    public function setLanguages(array $languages = null)
    {
        $this->languages = $languages !== null ? $languages : array();
    }

    /**
     * Provides autocomplete data for tag field edit interface.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function autoCompleteAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ez:tags:read');

        $subTreeLimit = (int) $request->query->get('subTreeLimit');
        $hideRootTag = (bool) $request->query->get('hideRootTag');

        $searchResult = $this->tagsService->searchTags(
            $request->query->get('searchString'),
            $request->query->get('locale')
        );

        $data = $data = $this->filterTags($searchResult->tags, $subTreeLimit, $hideRootTag);

        return new JsonResponse($data);
    }

    /**
     * Provides tag children data for tag field edit interface.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function childrenAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ez:tags:read');

        $subTreeLimit = (int) $request->query->get('subTreeLimit', 0);
        $hideRootTag = (bool) $request->query->get('hideRootTag', false);
        $locale = $request->query->get('locale');

        $tags = $this->tagsService->loadTagChildren(
            !empty($subTreeLimit) ? $this->tagsService->loadTag($subTreeLimit) : null,
            0,
            -1,
            array($locale)
        );

        $data = $this->filterTags($tags, $subTreeLimit, $hideRootTag);

        return new JsonResponse($data);
    }

    private function filterTags(array $tags, $subTreeLimit, $hideRootTag)
    {
        $data = array();
        foreach ($tags as $tag) {
            if ($subTreeLimit > 0 && !in_array($subTreeLimit, $tag->path, true)) {
                continue;
            }

            if ($hideRootTag && $tag->id === $subTreeLimit) {
                continue;
            }

            $tagKeywords = $tag->getKeywords($this->languages);

            $parentTagKeywords = array();
            if ($tag->hasParent()) {
                $parentTag = $this->tagsService->loadTag($tag->parentTagId);
                $parentTagKeywords = $parentTag->getKeywords($this->languages);
            }

            $data[] = array(
                'parent_id' => $tag->parentTagId,
                'parent_name' => !empty($parentTagKeywords) ? array_values($parentTagKeywords)[0] : '',
                'name' => array_values($tagKeywords)[0],
                'id' => $tag->id,
                'main_tag_id' => $tag->mainTagId,
                'locale' => array_keys($tagKeywords)[0],
            );
        }

        return $data;
    }
}
