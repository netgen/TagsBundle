<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Controller;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use EzSystems\EzPlatformRest\Exceptions;
use EzSystems\EzPlatformRest\Exceptions\ForbiddenException;
use EzSystems\EzPlatformRest\Message;
use EzSystems\EzPlatformRest\Server\Controller as RestController;
use EzSystems\EzPlatformRest\Server\Exceptions\BadRequestException;
use EzSystems\EzPlatformRest\Server\Values as BaseValues;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Core\REST\Values;
use Symfony\Component\HttpFoundation\Request;
use function array_pop;
use function explode;
use function in_array;
use function trim;

final class Tags extends RestController
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * Loads the tag for a given ID (x)or remote ID.
     *
     * @throws \EzSystems\EzPlatformRest\Server\Exceptions\BadRequestException If the request does not have an ID or remote ID
     */
    public function redirectTag(Request $request): BaseValues\TemporaryRedirect
    {
        if (!$request->query->has('id') && !$request->query->has('remoteId')) {
            throw new BadRequestException("At least one of 'id' or 'remoteId' parameters is required.");
        }

        if ($request->query->has('id')) {
            $tag = $this->tagsService->loadTag($request->query->get('id'));
        } else {
            $tag = $this->tagsService->loadTagByRemoteId($request->query->get('remoteId'));
        }

        return new BaseValues\TemporaryRedirect(
            $this->router->generate(
                'ezpublish_rest_eztags_loadTag',
                [
                    'tagPath' => trim($tag->pathString, '/'),
                ]
            )
        );
    }

    /**
     * Loads a tag object by its path.
     *
     * @throws \EzSystems\EzPlatformRest\Exceptions\NotFoundException If no tag is found with specified path
     */
    public function loadTag(string $tagPath): Values\CachedValue
    {
        $tag = $this->tagsService->loadTag(
            $this->extractTagIdFromPath($tagPath)
        );

        if (trim($tag->pathString, '/') !== $tagPath) {
            throw new Exceptions\NotFoundException(
                "Could not find tag with path string {$tagPath}"
            );
        }

        $childrenCount = 0;
        $synonymsCount = 0;

        if (in_array($tag->mainTagId, [null, 0], true)) {
            $childrenCount = $this->tagsService->getTagChildrenCount($tag);
            $synonymsCount = $this->tagsService->getTagSynonymCount($tag);
        }

        return new Values\CachedValue(
            new Values\RestTag(
                $tag,
                $childrenCount,
                $synonymsCount
            ),
            ['tagId' => $tag->id]
        );
    }

    /**
     * Loads all tags with specified keyword.
     *
     * @throws \EzSystems\EzPlatformRest\Exceptions\NotFoundException If no tag is found with specified path
     */
    public function loadTagsByKeyword(string $keyword, string $language, Request $request): Values\CachedValue
    {
        $offset = $request->query->has('offset') ? (int) $request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int) $request->query->get('limit') : 25;

        $tags = $this->tagsService->loadTagsByKeyword(
            $keyword,
            $language,
            true,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25
        );

        $restTags = [];
        foreach ($tags as $tag) {
            $restTags[] = new Values\RestTag($tag, 0, 0);
        }

        return new Values\CachedValue(
            new Values\TagList(
                $restTags,
                $request->getPathInfo()
            ),
            ['tagKeyword' => $keyword . '|#' . $language]
        );
    }

    /**
     * Loads children of a tag object.
     */
    public function loadTagChildren(string $tagPath, Request $request): Values\CachedValue
    {
        $offset = $request->query->has('offset') ? (int) $request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int) $request->query->get('limit') : 25;

        $tagId = $this->extractTagIdFromPath($tagPath);
        $children = $this->tagsService->loadTagChildren(
            $tagId !== 0 ?
                $this->tagsService->loadTag($tagId) :
                null,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25
        );

        $restTags = [];
        foreach ($children as $tag) {
            $restTags[] = new Values\RestTag($tag, 0, 0);
        }

        return new Values\CachedValue(
            new Values\TagList(
                $restTags,
                $request->getPathInfo()
            ),
            ['tagId' => $tagId]
        );
    }

    /**
     * Loads synonyms of a tag object.
     */
    public function loadTagSynonyms(string $tagPath, Request $request): Values\CachedValue
    {
        $offset = $request->query->has('offset') ? (int) $request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int) $request->query->get('limit') : 25;

        $tagId = $this->extractTagIdFromPath($tagPath);
        $synonyms = $this->tagsService->loadTagSynonyms(
            $this->tagsService->loadTag($tagId),
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25
        );

        $restSynonyms = [];
        foreach ($synonyms as $synonym) {
            $restSynonyms[] = new Values\RestTag($synonym, 0, 0);
        }

        return new Values\CachedValue(
            new Values\TagList(
                $restSynonyms,
                $request->getPathInfo()
            ),
            ['tagId' => $tagId]
        );
    }

    /**
     * Returns content related to a tag.
     */
    public function getRelatedContent(string $tagPath, Request $request): Values\CachedValue
    {
        $offset = $request->query->has('offset') ? (int) $request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int) $request->query->get('limit') : 25;

        $tagId = $this->extractTagIdFromPath($tagPath);
        $relatedContent = $this->tagsService->getRelatedContent(
            $this->tagsService->loadTag($tagId),
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25
        );

        $restContent = [];
        /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo */
        foreach ($relatedContent as $contentInfo) {
            $restContent[] = new BaseValues\RestContent($contentInfo);
        }

        return new Values\CachedValue(
            new Values\ContentList(
                $restContent,
                $request->getPathInfo()
            ),
            ['tagId' => $tagId]
        );
    }

    /**
     * Creates a new tag.
     *
     * @throws \EzSystems\EzPlatformRest\Exceptions\ForbiddenException If there was an error while creating the tag
     */
    public function createTag(Request $request): Values\CreatedTag
    {
        $synonymCreateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            $createdTag = $this->tagsService->createTag($synonymCreateStruct);
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException($e->getMessage());
        }

        return new Values\CreatedTag(new Values\RestTag($createdTag, 0, 0));
    }

    /**
     * Creates a new synonym.
     *
     * @throws \EzSystems\EzPlatformRest\Exceptions\ForbiddenException If there was an error while creating the tag
     */
    public function createSynonym(string $tagPath, Request $request): Values\CreatedTag
    {
        $synonymCreateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $synonymCreateStruct->mainTagId = $this->extractTagIdFromPath($tagPath);

        try {
            $createdSynonym = $this->tagsService->addSynonym($synonymCreateStruct);
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException($e->getMessage());
        }

        return new Values\CreatedTag(new Values\RestTag($createdSynonym, 0, 0));
    }

    /**
     * Updates a tag.
     */
    public function updateTag(string $tagPath, Request $request): Values\RestTag
    {
        $tagUpdateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $tag = $this->tagsService->loadTag(
            $this->extractTagIdFromPath($tagPath)
        );

        $updatedTag = $this->tagsService->updateTag($tag, $tagUpdateStruct);

        $childrenCount = 0;
        $synonymsCount = 0;

        if (in_array($updatedTag->mainTagId, [null, 0], true)) {
            $childrenCount = $this->tagsService->getTagChildrenCount($updatedTag);
            $synonymsCount = $this->tagsService->getTagSynonymCount($updatedTag);
        }

        return new Values\RestTag(
            $updatedTag,
            $childrenCount,
            $synonymsCount
        );
    }

    /**
     * Copies a subtree to a new destination.
     *
     * @throws \EzSystems\EzPlatformRest\Server\Exceptions\BadRequestException if the Destination header cannot be parsed as a tag
     */
    public function copySubtree(string $tagPath, Request $request): BaseValues\ResourceCreated
    {
        $tag = $this->tagsService->loadTag(
            $this->extractTagIdFromPath($tagPath)
        );

        $destinationHref = $request->headers->get('Destination', '');

        try {
            /** @var string $parsedDestinationHref */
            $parsedDestinationHref = $this->requestParser->parseHref(
                $destinationHref,
                'tagPath'
            );
        } catch (Exceptions\InvalidArgumentException $e) {
            throw new BadRequestException("{$destinationHref} is not an acceptable destination");
        }

        $destinationTag = $this->tagsService->loadTag(
            $this->extractTagIdFromPath(
                $parsedDestinationHref
            )
        );

        $newTag = $this->tagsService->copySubtree($tag, $destinationTag);

        return new BaseValues\ResourceCreated(
            $this->router->generate(
                'ezpublish_rest_eztags_loadTag',
                [
                    'tagPath' => trim($newTag->pathString, '/'),
                ]
            )
        );
    }

    /**
     * Moves a subtree to a new location.
     *
     * @throws \EzSystems\EzPlatformRest\Server\Exceptions\BadRequestException if the Destination header cannot be parsed as a tag
     */
    public function moveSubtree(string $tagPath, Request $request): BaseValues\ResourceCreated
    {
        $tag = $this->tagsService->loadTag(
            $this->extractTagIdFromPath($tagPath)
        );

        $destinationHref = $request->headers->get('Destination', '');

        try {
            /** @var string $parsedDestinationHref */
            $parsedDestinationHref = $this->requestParser->parseHref(
                $destinationHref,
                'tagPath'
            );
        } catch (Exceptions\InvalidArgumentException $e) {
            throw new BadRequestException("{$destinationHref} is not an acceptable destination");
        }

        $destinationTag = $this->tagsService->loadTag(
            $this->extractTagIdFromPath(
                $parsedDestinationHref
            )
        );

        $this->tagsService->moveSubtree($tag, $destinationTag);

        // Reload the tag to get the new position is subtree
        $tag = $this->tagsService->loadTag($tag->id);

        return new BaseValues\ResourceCreated(
            $this->router->generate(
                'ezpublish_rest_eztags_loadTag',
                [
                    'tagPath' => trim($tag->pathString, '/'),
                ]
            )
        );
    }

    /**
     * Converts a tag to synonym.
     *
     * @throws \EzSystems\EzPlatformRest\Server\Exceptions\BadRequestException if the Destination header cannot be parsed as a tag
     */
    public function convertToSynonym(string $tagPath, Request $request): BaseValues\ResourceCreated
    {
        $tag = $this->tagsService->loadTag(
            $this->extractTagIdFromPath($tagPath)
        );

        $destinationHref = $request->headers->get('Destination', '');

        try {
            /** @var string $parsedDestinationHref */
            $parsedDestinationHref = $this->requestParser->parseHref(
                $destinationHref,
                'tagPath'
            );
        } catch (Exceptions\InvalidArgumentException $e) {
            throw new BadRequestException("{$destinationHref} is not an acceptable destination");
        }

        $mainTag = $this->tagsService->loadTag(
            $this->extractTagIdFromPath(
                $parsedDestinationHref
            )
        );

        $convertedTag = $this->tagsService->convertToSynonym($tag, $mainTag);

        return new BaseValues\ResourceCreated(
            $this->router->generate(
                'ezpublish_rest_eztags_loadTag',
                [
                    'tagPath' => trim($convertedTag->pathString, '/'),
                ]
            )
        );
    }

    /**
     * Merges two tags.
     *
     * @throws \EzSystems\EzPlatformRest\Server\Exceptions\BadRequestException if the Destination header cannot be parsed as a tag
     */
    public function mergeTags(string $tagPath, Request $request): BaseValues\NoContent
    {
        $tag = $this->tagsService->loadTag(
            $this->extractTagIdFromPath($tagPath)
        );

        $destinationHref = $request->headers->get('Destination', '');

        try {
            /** @var string $parsedDestinationHref */
            $parsedDestinationHref = $this->requestParser->parseHref(
                $destinationHref,
                'tagPath'
            );
        } catch (Exceptions\InvalidArgumentException $e) {
            throw new BadRequestException("{$destinationHref} is not an acceptable destination");
        }

        $targetTag = $this->tagsService->loadTag(
            $this->extractTagIdFromPath(
                $parsedDestinationHref
            )
        );

        $this->tagsService->mergeTags($tag, $targetTag);

        return new BaseValues\NoContent();
    }

    /**
     * Deletes a tag.
     */
    public function deleteTag(string $tagPath): BaseValues\NoContent
    {
        $tag = $this->tagsService->loadTag(
            $this->extractTagIdFromPath($tagPath)
        );

        $this->tagsService->deleteTag($tag);

        return new BaseValues\NoContent();
    }

    /**
     * Extracts and returns an item ID from a path, e.g. /1/2/42/ => 42.
     */
    private function extractTagIdFromPath(string $path): int
    {
        $pathParts = explode('/', trim($path, '/'));

        return (int) array_pop($pathParts);
    }
}
