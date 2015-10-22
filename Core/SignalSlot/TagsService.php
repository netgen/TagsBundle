<?php

namespace Netgen\TagsBundle\Core\SignalSlot;

use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use Netgen\TagsBundle\API\Repository\TagsService as TagsServiceInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\CreateTagSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\UpdateTagSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\AddSynonymSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\ConvertToSynonymSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\MergeTagsSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\CopySubtreeSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\MoveSubtreeSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\DeleteTagSignal;

class TagsService implements TagsServiceInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $service;

    /**
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(TagsServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        $this->service = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * Loads a tag object from its $tagId.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     * @param array|null $languages A language filter for keywords. If not given all languages are returned.
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTag($tagId, array $languages = null, $useAlwaysAvailable = true)
    {
        return $this->service->loadTag($tagId, $languages, $useAlwaysAvailable);
    }

    /**
     * Loads a tag object from its $remoteId.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param string $remoteId
     * @param array|null $languages A language filter for keywords. If not given all languages are returned.
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTagByRemoteId($remoteId, array $languages = null, $useAlwaysAvailable = true)
    {
        return $this->service->loadTagByRemoteId($remoteId, $languages, $useAlwaysAvailable);
    }

    /**
     * Loads a tag object from its URL.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param string $url
     * @param string[] $languages
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTagByUrl($url, array $languages)
    {
        return $this->service->loadTagByUrl($url, $languages);
    }

    /**
     * Loads children of a tag object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag If null, tags from the first level will be returned
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all children starting at $offset are returned
     * @param array|null $languages A language filter for keywords. If not given all languages are returned.
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public function loadTagChildren(Tag $tag = null, $offset = 0, $limit = -1, array $languages = null, $useAlwaysAvailable = true)
    {
        return $this->service->loadTagChildren($tag, $offset, $limit, $languages, $useAlwaysAvailable);
    }

    /**
     * Returns the number of children of a tag object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag If null, tag count from the first level will be returned
     * @param array|null $languages A language filter for keywords. If not given all languages are returned.
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @return int
     */
    public function getTagChildrenCount(Tag $tag = null, array $languages = null, $useAlwaysAvailable = true)
    {
        return $this->service->getTagChildrenCount($tag, $languages, $useAlwaysAvailable);
    }

    /**
     * Loads tags by specified keyword.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @param string $keyword The keyword to fetch tags for
     * @param string $language The language to check for
     * @param bool $useAlwaysAvailable Check for main language if true (default) and if tag is always available
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all children starting at $offset are returned
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public function loadTagsByKeyword($keyword, $language, $useAlwaysAvailable = true, $offset = 0, $limit = -1)
    {
        return $this->service->loadTagsByKeyword($keyword, $language, $useAlwaysAvailable, $offset, $limit);
    }

    /**
     * Returns the number of tags by specified keyword.
     *
     * @param string $keyword The keyword to fetch tags count for
     * @param string $language The language to check for
     * @param bool $useAlwaysAvailable Check for main language if true (default) and if tag is always available
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @return int
     */
    public function getTagsByKeywordCount($keyword, $language, $useAlwaysAvailable = true)
    {
        return $this->service->getTagsByKeywordCount($keyword, $language, $useAlwaysAvailable);
    }

    /**
     * Loads synonyms of a tag object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the tag is already a synonym
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param int $offset The start offset for paging
     * @param int $limit The number of synonyms returned. If $limit = -1 all synonyms starting at $offset are returned
     * @param array|null $languages A language filter for keywords. If not given all languages are returned.
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public function loadTagSynonyms(Tag $tag, $offset = 0, $limit = -1, array $languages = null, $useAlwaysAvailable = true)
    {
        return $this->service->loadTagSynonyms($tag, $offset, $limit, $languages, $useAlwaysAvailable);
    }

    /**
     * Returns the number of synonyms of a tag object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the tag is already a synonym
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param array|null $languages A language filter for keywords. If not given all languages are returned.
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @return int
     */
    public function getTagSynonymCount(Tag $tag, array $languages = null, $useAlwaysAvailable = true)
    {
        return $this->service->getTagSynonymCount($tag, $languages, $useAlwaysAvailable);
    }

    /**
     * Loads content related to $tag.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param int $offset The start offset for paging
     * @param int $limit The number of content objects returned. If $limit = -1 all content objects starting at $offset are returned
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     */
    public function getRelatedContent(Tag $tag, $offset = 0, $limit = -1)
    {
        return $this->service->getRelatedContent($tag, $offset, $limit);
    }

    /**
     * Returns the number of content objects related to $tag.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return int
     */
    public function getRelatedContentCount(Tag $tag)
    {
        return $this->service->getRelatedContentCount($tag);
    }

    /**
     * Creates the new tag.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to create this tag
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the remote ID already exists
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct $tagCreateStruct
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag The newly created tag
     */
    public function createTag(TagCreateStruct $tagCreateStruct)
    {
        $returnValue = $this->service->createTag($tagCreateStruct);
        $this->signalDispatcher->emit(
            new CreateTagSignal(
                array(
                    'tagId' => $returnValue->id,
                    'parentTagId' => $returnValue->parentTagId,
                    'keywords' => $returnValue->keywords,
                    'mainLanguageCode' => $returnValue->mainLanguageCode,
                    'alwaysAvailable' => $returnValue->alwaysAvailable,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Updates $tag.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to update this tag
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the remote ID already exists
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct $tagUpdateStruct
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag The updated tag
     */
    public function updateTag(Tag $tag, TagUpdateStruct $tagUpdateStruct)
    {
        $returnValue = $this->service->updateTag($tag, $tagUpdateStruct);
        $this->signalDispatcher->emit(
            new UpdateTagSignal(
                array(
                    'tagId' => $returnValue->id,
                    'keywords' => $returnValue->keywords,
                    'remoteId' => $returnValue->remoteId,
                    'mainLanguageCode' => $returnValue->mainLanguageCode,
                    'alwaysAvailable' => $returnValue->alwaysAvailable,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Creates a synonym for $tag.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to create a synonym
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target tag is a synonym
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct $synonymCreateStruct
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag The created synonym
     */
    public function addSynonym(SynonymCreateStruct $synonymCreateStruct)
    {
        $returnValue = $this->service->addSynonym($synonymCreateStruct);
        $this->signalDispatcher->emit(
            new AddSynonymSignal(
                array(
                    'tagId' => $returnValue->id,
                    'mainTagId' => $returnValue->mainTagId,
                    'keywords' => $returnValue->keywords,
                    'mainLanguageCode' => $returnValue->mainLanguageCode,
                    'alwaysAvailable' => $returnValue->alwaysAvailable,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Converts $tag to a synonym of $mainTag.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to convert tag to synonym
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If either one of the tags is a synonym
     *                                                                        If the main tag is a sub tag of the given tag
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $mainTag
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag The converted synonym
     */
    public function convertToSynonym(Tag $tag, Tag $mainTag)
    {
        $returnValue = $this->service->convertToSynonym($tag, $mainTag);
        $this->signalDispatcher->emit(
            new ConvertToSynonymSignal(
                array(
                    'tagId' => $returnValue->id,
                    'mainTagId' => $returnValue->mainTagId,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Merges the $tag into the $targetTag.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to merge tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If either one of the tags is a synonym
     *                                                                        If the target tag is a sub tag of the given tag
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $targetTag
     */
    public function mergeTags(Tag $tag, Tag $targetTag)
    {
        $this->service->mergeTags($tag, $targetTag);
        $this->signalDispatcher->emit(
            new MergeTagsSignal(
                array(
                    'tagId' => $tag->id,
                    'targetTagId' => $targetTag->id,
                )
            )
        );
    }

    /**
     * Copies the subtree starting from $tag as a new subtree of $targetParentTag.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target tag is a sub tag of the given tag
     *                                                                        If the target tag is already a parent of the given tag
     *                                                                        If either one of the tags is a synonym
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag The subtree denoted by the tag to copy
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $targetParentTag The target parent tag for the copy operation
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag The newly created tag of the copied subtree
     */
    public function copySubtree(Tag $tag, Tag $targetParentTag)
    {
        $returnValue = $this->service->copySubtree($tag, $targetParentTag);
        $this->signalDispatcher->emit(
            new CopySubtreeSignal(
                array(
                    'sourceTagId' => $tag->id,
                    'targetParentTagId' => $targetParentTag->id,
                    'newTagId' => $returnValue->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Moves the subtree to $targetParentTag.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to move this tag
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target tag is a sub tag of the given tag
     *                                                                        If the target tag is already a parent of the given tag
     *                                                                        If either one of the tags is a synonym
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $targetParentTag
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag The updated root tag of the moved subtree
     */
    public function moveSubtree(Tag $tag, Tag $targetParentTag)
    {
        $returnValue = $this->service->moveSubtree($tag, $targetParentTag);
        $this->signalDispatcher->emit(
            new MoveSubtreeSignal(
                array(
                    'sourceTagId' => $tag->id,
                    'targetParentTagId' => $targetParentTag->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Deletes $tag and all its descendants and synonyms.
     *
     * If $tag is a synonym, only the synonym is deleted
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to delete this tag
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     */
    public function deleteTag(Tag $tag)
    {
        $this->service->deleteTag($tag);
        $this->signalDispatcher->emit(
            new DeleteTagSignal(
                array(
                    'tagId' => $tag->id,
                )
            )
        );
    }

    /**
     * Instantiates a new tag create struct.
     *
     * @param mixed $parentTagId
     * @param string $mainLanguageCode
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct
     */
    public function newTagCreateStruct($parentTagId, $mainLanguageCode)
    {
        return $this->service->newTagCreateStruct($parentTagId, $mainLanguageCode);
    }

    /**
     * Instantiates a new synonym create struct.
     *
     * @param mixed $mainTagId
     * @param string $mainLanguageCode
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct
     */
    public function newSynonymCreateStruct($mainTagId, $mainLanguageCode)
    {
        return $this->service->newSynonymCreateStruct($mainTagId, $mainLanguageCode);
    }

    /**
     * Instantiates a new tag update struct.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct
     */
    public function newTagUpdateStruct()
    {
        return $this->service->newTagUpdateStruct();
    }

    /**
     * Allows API execution to be performed with full access sand-boxed.
     *
     * The closure sandbox will do a catch all on exceptions and rethrow after
     * re-setting the sudo nesting level.
     *
     * Example use:
     *     $tag = $tagsService->sudo(
     *         function (TagsService $tagsService) use ($tagCreateStruct) {
     *             return $tagsService->createTag($tagCreateStruct)
     *         }
     *     );
     *
     *
     * @param \Closure $callback
     *
     * @throws \RuntimeException Thrown on recursive sudo() use.
     * @throws \Exception Re throws exceptions thrown inside $callback
     *
     * @return mixed
     */
    public function sudo(\Closure $callback)
    {
        return $this->service->sudo($callback, $this);
    }

}
