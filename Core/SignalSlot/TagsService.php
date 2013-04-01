<?php

namespace EzSystems\TagsBundle\Core\SignalSlot;

use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use EzSystems\TagsBundle\API\Repository\TagsService as TagsServiceInterface;
use EzSystems\TagsBundle\API\Repository\Values\Tags\Tag;
use EzSystems\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use EzSystems\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;

use EzSystems\TagsBundle\Core\SignalSlot\Signal\TagsService\CreateTagSignal;
use EzSystems\TagsBundle\Core\SignalSlot\Signal\TagsService\UpdateTagSignal;
use EzSystems\TagsBundle\Core\SignalSlot\Signal\TagsService\AddSynonymSignal;
use EzSystems\TagsBundle\Core\SignalSlot\Signal\TagsService\DeleteTagSignal;

class TagsService implements TagsServiceInterface
{
    /**
     * @var \EzSystems\TagsBundle\API\Repository\TagsService
     */
    protected $service;

    /**
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * @param \EzSystems\TagsBundle\API\Repository\TagsService $service
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read this tag
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     *
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTag( $tagId )
    {
        return $this->service->loadTag( $tagId );
    }

    /**
     * Loads a tag object from its $remoteId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read this tag
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param string $remoteId
     *
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTagByRemoteId( $remoteId )
    {
        return $this->service->loadTagByRemoteId( $remoteId );
    }

    /**
     * Loads children of a tag object
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = 0 all children starting at $offset are returned
     *
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public function loadTagChildren( Tag $tag, $offset = 0, $limit = 0 )
    {
        return $this->service->loadTagChildren( $tag, $offset, $limit );
    }

    /**
     * Returns the number of children of a tag object
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return int
     */
    public function getTagChildrenCount( Tag $tag )
    {
        return $this->service->getTagChildrenCount( $tag );
    }

    /**
     * Creates the new tag
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to create this tag
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the remote ID already exists
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\TagCreateStruct $tagCreateStruct
     *
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag The newly created tag
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
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct $tagUpdateStruct
     *
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag The updated tag
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to create a synonym
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param string $keyword
     *
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag The created synonym
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to convert tag to synonym
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException Tf the tag is already a synonym
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $mainTag
     *
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag The converted synonym
     */
    public function convertToSynonym( Tag $tag, Tag $mainTag )
    {
        return $this->service->convertToSynonym( $tag, $mainTag );
    }

    /**
     * Merges the $tag into the $targetTag
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to merge tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If either one of the tags is a synonym
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $targetTag
     */
    public function mergeTags( Tag $tag, Tag $targetTag )
    {
        $this->service->mergeTags( $tag, $targetTag );
    }

    /**
     * Swaps the locations of $tag1 and $tag2
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to swap tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If either one of the tags is a synonym
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag1
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag2
     */
    public function swapTag( Tag $tag1, Tag $tag2 )
    {
        $this->service->swapTag( $tag1, $tag2 );
    }

    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetParentTag
     *
     * Only the items on which the user has read access are copied
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed copy the subtree to the given parent tag
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user does not have read access to the whole source subtree
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target tag is a sub tag of the given tag
     *                                                                        If either one of the tags is a synonym
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $subtree The subtree denoted by the tag to copy
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $targetParentTag The target parent tag for the copy operation
     *
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag The newly created tag of the copied subtree
     */
    public function copySubtree( Tag $subtree, Tag $targetParentTag )
    {
        return $this->service->copySubtree( $subtree, $targetParentTag );
    }

    /**
     * Moves the subtree to $newParentTag
     *
     * If a user has the permission to move the tag to a target tag
     * he can do it regardless of an existing descendant on which the user has no permission
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to move this tag to the target
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user does not have read access to the whole source subtree
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If either one of the tags is a synonym
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $newParentTag
     */
    public function moveSubtree( Tag $tag, Tag $newParentTag )
    {
        $this->service->moveSubtree( $tag, $newParentTag );
    }

    /**
     * Deletes $tag and all its descendants and synonyms
     *
     * If $tag is a synonym, only the synonym is deleted
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to delete this tag or a descendant
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
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
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\TagCreateStruct
     */
    public function newTagCreateStruct( $parentTagId, $keyword )
    {
        return $this->service->newTagCreateStruct( $parentTagId, $keyword );
    }

    /**
     * Instantiates a new tag update struct
     *
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct
     */
    public function newTagUpdateStruct()
    {
        return $this->service->newTagUpdateStruct();
    }
}
