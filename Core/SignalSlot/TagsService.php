<?php

namespace Netgen\TagsBundle\Core\SignalSlot;

use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use Netgen\TagsBundle\API\Repository\TagsService as TagsServiceInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;

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
     * Constructor
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct( TagsServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * Loads a tag object from its $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTag( $tagId )
    {
        return $this->service->loadTag( $tagId );
    }

    /**
     * Loads a tag object from its $remoteId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param string $remoteId
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTagByRemoteId( $remoteId )
    {
        return $this->service->loadTagByRemoteId( $remoteId );
    }

    /**
     * Loads a tag object from its URL
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param string $url
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTagByUrl( $url )
    {
        return $this->service->loadTagByUrl( $url );
    }

    /**
     * Loads children of a tag object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag If null, tags from the first level will be returned
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all children starting at $offset are returned
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public function loadTagChildren( Tag $tag = null, $offset = 0, $limit = -1 )
    {
        return $this->service->loadTagChildren( $tag, $offset, $limit );
    }

    /**
     * Returns the number of children of a tag object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag If null, tag count from the first level will be returned
     *
     * @return int
     */
    public function getTagChildrenCount( Tag $tag = null )
    {
        return $this->service->getTagChildrenCount( $tag );
    }

    /**
     * Loads tags by specified keyword
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @param string $keyword The keyword to fetch tags for
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all children starting at $offset are returned
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public function loadTagsByKeyword( $keyword, $offset = 0, $limit = -1 )
    {
        return $this->service->loadTagsByKeyword( $keyword, $offset, $limit );
    }

    /**
     * Returns the number of tags by specified keyword
     *
     * @param string $keyword The keyword to fetch tags count for
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @return int
     */
    public function getTagsByKeywordCount( $keyword )
    {
        return $this->service->getTagsByKeywordCount( $keyword );
    }

    /**
     * Loads synonyms of a tag object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the tag is already a synonym
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param int $offset The start offset for paging
     * @param int $limit The number of synonyms returned. If $limit = -1 all synonyms starting at $offset are returned
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public function loadTagSynonyms( Tag $tag, $offset = 0, $limit = -1 )
    {
        return $this->service->loadTagSynonyms( $tag, $offset, $limit );
    }

    /**
     * Returns the number of synonyms of a tag object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the tag is already a synonym
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return int
     */
    public function getTagSynonymCount( Tag $tag )
    {
        return $this->service->getTagSynonymCount( $tag );
    }

    /**
     * Loads content related to $tag
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
    public function getRelatedContent( Tag $tag, $offset = 0, $limit = -1 )
    {
        return $this->service->getRelatedContent( $tag, $offset, $limit );
    }

    /**
     * Returns the number of content objects related to $tag
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return int
     */
    public function getRelatedContentCount( Tag $tag )
    {
        return $this->service->getRelatedContentCount( $tag );
    }

    /**
     * Creates the new tag
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to create this tag
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the remote ID already exists
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct $tagCreateStruct
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag The newly created tag
     */
    public function createTag( TagCreateStruct $tagCreateStruct )
    {
        $returnValue = $this->service->createTag( $tagCreateStruct );
        $this->signalDispatcher->emit(
            new CreateTagSignal(
                array(
                    "tagId" => $returnValue->id,
                    "parentTagId" => $returnValue->parentTagId,
                    "keyword" => $returnValue->keyword
                )
            )
        );

        return $returnValue;
    }

    /**
     * Updates $tag
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
    public function updateTag( Tag $tag, TagUpdateStruct $tagUpdateStruct )
    {
        $returnValue = $this->service->updateTag( $tag, $tagUpdateStruct );
        $this->signalDispatcher->emit(
            new UpdateTagSignal(
                array(
                    "tagId" => $returnValue->id,
                    "keyword" => $returnValue->keyword,
                    "remoteId" => $returnValue->remoteId
                )
            )
        );

        return $returnValue;
    }

    /**
     * Creates a synonym for $tag
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to create a synonym
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target tag is a synonym
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param string $keyword
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag The created synonym
     */
    public function addSynonym( Tag $tag, $keyword )
    {
        $returnValue = $this->service->addSynonym( $tag, $keyword );
        $this->signalDispatcher->emit(
            new AddSynonymSignal(
                array(
                    "tagId" => $returnValue->id,
                    "mainTagId" => $returnValue->mainTagId,
                    "keyword" => $returnValue->keyword
                )
            )
        );

        return $returnValue;
    }

    /**
     * Converts $tag to a synonym of $mainTag
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
    public function convertToSynonym( Tag $tag, Tag $mainTag )
    {
        $returnValue = $this->service->convertToSynonym( $tag, $mainTag );
        $this->signalDispatcher->emit(
            new ConvertToSynonymSignal(
                array(
                    "tagId" => $returnValue->id,
                    "mainTagId" => $returnValue->mainTagId
                )
            )
        );

        return $returnValue;
    }

    /**
     * Merges the $tag into the $targetTag
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to merge tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If either one of the tags is a synonym
     *                                                                        If the target tag is a sub tag of the given tag
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $targetTag
     */
    public function mergeTags( Tag $tag, Tag $targetTag )
    {
        $this->service->mergeTags( $tag, $targetTag );
        $this->signalDispatcher->emit(
            new MergeTagsSignal(
                array(
                    "tagId" => $tag->id,
                    "targetTagId" => $targetTag->id
                )
            )
        );
    }

    /**
     * Copies the subtree starting from $tag as a new subtree of $targetParentTag
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
    public function copySubtree( Tag $tag, Tag $targetParentTag )
    {
        $returnValue = $this->service->copySubtree( $tag, $targetParentTag );
        $this->signalDispatcher->emit(
            new CopySubtreeSignal(
                array(
                    "sourceTagId" => $tag->id,
                    "targetParentTagId" => $targetParentTag->id,
                    "newTagId" => $returnValue->id
                )
            )
        );

        return $returnValue;
    }

    /**
     * Moves the subtree to $targetParentTag
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
    public function moveSubtree( Tag $tag, Tag $targetParentTag )
    {
        $returnValue = $this->service->moveSubtree( $tag, $targetParentTag );
        $this->signalDispatcher->emit(
            new MoveSubtreeSignal(
                array(
                    "sourceTagId" => $tag->id,
                    "targetParentTagId" => $targetParentTag->id
                )
            )
        );

        return $returnValue;
    }

    /**
     * Deletes $tag and all its descendants and synonyms
     *
     * If $tag is a synonym, only the synonym is deleted
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to delete this tag
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     */
    public function deleteTag( Tag $tag )
    {
        $this->service->deleteTag( $tag );
        $this->signalDispatcher->emit(
            new DeleteTagSignal(
                array(
                    "tagId" => $tag->id
                )
            )
        );
    }

    /**
     * Instantiates a new tag create struct
     *
     * @param mixed $parentTagId
     * @param string $keyword
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct
     */
    public function newTagCreateStruct( $parentTagId, $keyword )
    {
        return $this->service->newTagCreateStruct( $parentTagId, $keyword );
    }

    /**
     * Instantiates a new tag update struct
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct
     */
    public function newTagUpdateStruct()
    {
        return $this->service->newTagUpdateStruct();
    }
}
