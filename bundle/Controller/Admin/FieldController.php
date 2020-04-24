<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Controller\Admin;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagList;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use function array_keys;
use function array_values;
use function count;
use function in_array;

final class FieldController extends Controller
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    public function __construct(TagsService $tagsService, ConfigResolverInterface $configResolver)
    {
        $this->tagsService = $tagsService;
        $this->configResolver = $configResolver;
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

    private function filterTags(TagList $tags, int $subTreeLimit, bool $hideRootTag): array
    {
        $data = [];
        $languages = $this->configResolver->getParameter('languages');

        foreach ($tags as $tag) {
            if ($subTreeLimit > 0 && !in_array($subTreeLimit, $tag->path, true)) {
                continue;
            }

            if ($hideRootTag && $tag->id === $subTreeLimit) {
                continue;
            }

            $tagKeywords = $tag->getKeywords($languages);

            $parentTagKeywords = [];
            if ($tag->hasParent()) {
                $parentTag = $this->tagsService->loadTag($tag->parentTagId);
                $parentTagKeywords = $parentTag->getKeywords($languages);
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
