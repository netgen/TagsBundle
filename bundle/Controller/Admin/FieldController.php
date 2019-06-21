<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Controller\Admin;

use Netgen\TagsBundle\API\Repository\TagsService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @final
 */
class FieldController extends Controller
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var array
     */
    private $languages = [];

    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * Sets the list of available languages to controller.
     */
    public function setLanguages(?array $languages = null): void
    {
        $this->languages = $languages ?? [];
    }

    /**
     * Provides auto-complete data for tag field edit interface.
     */
    public function autoCompleteAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ez:tags:read');

        $subTreeLimit = $request->query->getInt('subTreeLimit');
        $hideRootTag = $request->query->getBoolean('hideRootTag');

        $searchResult = $this->tagsService->searchTags(
            $request->query->get('searchString'),
            $request->query->get('locale')
        );

        $data = $data = $this->filterTags($searchResult->tags, $subTreeLimit, $hideRootTag);

        return new JsonResponse($data);
    }

    /**
     * Provides tag children data for tag field edit interface.
     */
    public function childrenAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ez:tags:read');

        $subTreeLimit = $request->query->getInt('subTreeLimit');
        $hideRootTag = $request->query->getBoolean('hideRootTag');
        $locale = $request->query->get('locale');

        $tags = $this->tagsService->loadTagChildren(
            $subTreeLimit !== 0 ? $this->tagsService->loadTag($subTreeLimit) : null,
            0,
            -1,
            [$locale]
        );

        $data = $this->filterTags($tags, $subTreeLimit, $hideRootTag);

        return new JsonResponse($data);
    }

    private function filterTags(array $tags, int $subTreeLimit, bool $hideRootTag): array
    {
        $data = [];
        foreach ($tags as $tag) {
            if ($subTreeLimit > 0 && !in_array($subTreeLimit, $tag->path, true)) {
                continue;
            }

            if ($hideRootTag && $tag->id === $subTreeLimit) {
                continue;
            }

            $tagKeywords = $tag->getKeywords($this->languages);

            $parentTagKeywords = [];
            if ($tag->hasParent()) {
                $parentTag = $this->tagsService->loadTag($tag->parentTagId);
                $parentTagKeywords = $parentTag->getKeywords($this->languages);
            }

            $data[] = [
                'parent_id' => $tag->parentTagId,
                'parent_name' => count($parentTagKeywords) > 0 ? array_values($parentTagKeywords)[0] : '',
                'name' => array_values($tagKeywords)[0],
                'id' => $tag->id,
                'main_tag_id' => $tag->mainTagId,
                'locale' => array_keys($tagKeywords)[0],
            ];
        }

        return $data;
    }
}
