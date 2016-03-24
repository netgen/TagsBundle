<?php

namespace Netgen\TagsBundle\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Server\Controller as RestController;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Exceptions\BadRequestException;
use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;
use eZ\Publish\Core\REST\Server\Values as BaseValues;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Core\REST\Server\Values;
use Symfony\Component\HttpFoundation\Request;

class Tags extends RestController
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * Controller.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * Loads the tag for a given ID (x)or remote ID.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\BadRequestException If the request does not have an ID or remote ID.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\TemporaryRedirect
     */
    public function redirectTag(Request $request)
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
                array(
                    'tagPath' => trim($tag->pathString, '/'),
                )
            )
        );
    }

    /**
     * Loads a tag object by its path.
     *
     * @param string $tagPath
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException If no tag is found with specified path.
     *
     * @return \Netgen\TagsBundle\Core\REST\Server\Values\RestTag
     */
    public function loadTag($tagPath)
    {
        $tag = $this->tagsService->loadTag(
            $this->extractTagIdFromPath($tagPath)
        );

        if (trim($tag->pathString, '/') != $tagPath) {
            throw new Exceptions\NotFoundException(
                "Could not find tag with path string $tagPath"
            );
        }

        $childrenCount = 0;
        $synonymsCount = 0;

        if (empty($tag->mainTagId)) {
            $childrenCount = $this->tagsService->getTagChildrenCount($tag);
            $synonymsCount = $this->tagsService->getTagSynonymCount($tag);
        }

        return new Values\CachedValue(
            new Values\RestTag(
                $tag,
                $childrenCount,
                $synonymsCount
            ),
            array('tagId' => $tag->id)
        );
    }

    /**
     * Loads all tags with specified keyword.
     *
     * @param string $keyword
     * @param string $language
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException If no tag is found with specified path.
     *
     * @return \Netgen\TagsBundle\Core\REST\Server\Values\TagList[]
     */
    public function loadTagsByKeyword($keyword, $language, Request $request)
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 25;

        $tags = $this->tagsService->loadTagsByKeyword(
            $keyword,
            $language,
            true,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25
        );

        $restTags = array();
        foreach ($tags as $tag) {
            $restTags[] = new Values\RestTag($tag, 0, 0);
        }

        return new Values\CachedValue(
            new Values\TagList(
                $restTags,
                $request->getPathInfo()
            ),
            array('tagKeyword' => $keyword . '|#' . $language)
        );
    }

    /**
     * Loads children of a tag object.
     *
     * @param string $tagPath
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Netgen\TagsBundle\Core\REST\Server\Values\TagList
     */
    public function loadTagChildren($tagPath, Request $request)
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 25;

        $tagId = $this->extractTagIdFromPath($tagPath);
        $children = $this->tagsService->loadTagChildren(
            $tagId !== '0' ?
                $this->tagsService->loadTag($tagId) :
                null,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25
        );

        $restTags = array();
        foreach ($children as $tag) {
            $restTags[] = new Values\RestTag($tag, 0, 0);
        }

        return new Values\CachedValue(
            new Values\TagList(
                $restTags,
                $request->getPathInfo()
            ),
            array('tagId' => $tagId)
        );
    }

    /**
     * Loads synonyms of a tag object.
     *
     * @param string $tagPath
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Netgen\TagsBundle\Core\REST\Server\Values\TagList
     */
    public function loadTagSynonyms($tagPath, Request $request)
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 25;

        $tagId = $this->extractTagIdFromPath($tagPath);
        $synonyms = $this->tagsService->loadTagSynonyms(
            $this->tagsService->loadTag($tagId),
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25
        );

        $restSynonyms = array();
        foreach ($synonyms as $synonym) {
            $restSynonyms[] = new Values\RestTag($synonym, 0, 0);
        }

        return new Values\CachedValue(
            new Values\TagList(
                $restSynonyms,
                $request->getPathInfo()
            ),
            array('tagId' => $tagId)
        );
    }

    /**
     * Returns content related to a tag.
     *
     * @param string $tagPath
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Netgen\TagsBundle\Core\REST\Server\Values\TagList
     */
    public function getRelatedContent($tagPath, Request $request)
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 25;

        $tagId = $this->extractTagIdFromPath($tagPath);
        $relatedContent = $this->tagsService->getRelatedContent(
            $this->tagsService->loadTag($tagId),
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25,
            true
        );

        $restContent = array();
        foreach ($relatedContent as $contentInfo) {
            $restContent[] = new BaseValues\RestContent($contentInfo);
        }

        return new Values\CachedValue(
            new Values\ContentList(
                $restContent,
                $request->getPathInfo()
            ),
            array('tagId' => $tagId)
        );
    }

    /**
     * Creates a new tag.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException If there was an error while creating the tag.
     *
     * @return \Netgen\TagsBundle\Core\REST\Server\Values\CreatedTag
     */
    public function createTag(Request $request)
    {
        $tagCreateStruct = $this->inputDispatcher->parse(
            new Message(
                array('Content-Type' => $request->headers->get('Content-Type')),
                $request->getContent()
            )
        );

        try {
            $createdTag = $this->tagsService->createTag($tagCreateStruct);
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException($e->getMessage());
        }

        return new Values\CreatedTag(new Values\RestTag($createdTag, 0, 0));
    }

    /**
     * Extracts and returns an item ID from a path, e.g. /1/2/42/ => 42.
     *
     * @param string $path
     *
     * @return mixed
     */
    protected function extractTagIdFromPath($path)
    {
        $pathParts = explode('/', trim($path, '/'));

        return array_pop($pathParts);
    }
}
