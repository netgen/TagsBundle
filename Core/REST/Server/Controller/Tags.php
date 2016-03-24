<?php

namespace Netgen\TagsBundle\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Server\Controller as RestController;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Exceptions\BadRequestException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Values as BaseValues;
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
                'eztags_rest_loadTag',
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

        return new Values\CachedValue(
            new Values\RestTag(
                $tag,
                $this->tagsService->getTagChildrenCount($tag)
            ),
            array('tagId' => $tag->id)
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
            $restTags[] = new Values\RestTag(
                $tag,
                $this->tagsService->getTagChildrenCount($tag)
            );
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
