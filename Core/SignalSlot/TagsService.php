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
use EzSystems\TagsBundle\Core\SignalSlot\Signal\TagsService\ConvertToSynonymSignal;
use EzSystems\TagsBundle\Core\SignalSlot\Signal\TagsService\MergeTagsSignal;
use EzSystems\TagsBundle\Core\SignalSlot\Signal\TagsService\CopySubtreeSignal;
use EzSystems\TagsBundle\Core\SignalSlot\Signal\TagsService\MoveSubtreeSignal;
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
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
     * Loads synonyms of a tag object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the tag is already a synonym
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param int $offset The start offset for paging
     * @param int $limit The number of synonyms returned. If $limit = 0 all synonyms starting at $offset are returned
     *
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public function loadTagSynonyms( Tag $tag, $offset = 0, $limit = 0 )
    {
        return $this->loadTagSynonyms( $tag, $offset, $limit );
    }

    /**
     * Returns the number of synonyms of a tag object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the tag is already a synonym
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return int
     */
    public function getTagSynonymCount( Tag $tag )
    {
        return $this->getTagSynonymCount( $tag );
    }

    /**
     * Loads content related to $tag
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param int $offset The start offset for paging
     * @param int $limit The number of content objects returned. If $limit = 0 all content objects starting at $offset are returned
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     */
    public function getRelatedContent( Tag $tag, $offset = 0, $limit = 0 )
    {
        return $this->service->getRelatedContent( $tag, $offset, $limit );
    }

    /**
     * Returns the number of content objects related to $tag
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
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
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to create a synonym
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target tag is a synonym
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
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to convert tag to synonym
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If either one of the tags is a synonym
     *                                                                        If the main tag is a sub tag of the given tag
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $mainTag
     *
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag The converted synonym
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
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $targetTag
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
     * Only the items on which the user has read access are copied
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target tag is a sub tag of the given tag
     *                                                                        If the target tag is already a parent of the given tag
     *                                                                        If either one of the tags is a synonym
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag The subtree denoted by the tag to copy
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $targetParentTag The target parent tag for the copy operation
     *
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag The newly created tag of the copied subtree
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
     * If a user has the permission to move the tag to a target tag
     * he can do it regardless of an existing descendant on which the user has no permission
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to move this tag to the target
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user does not have read access to the whole source subtree
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target tag is a sub tag of the given tag
     *                                                                        If the target tag is already a parent of the given tag
     *                                                                        If either one of the tags is a synonym
     *
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag $targetParentTag
     */
    public function moveSubtree( Tag $tag, Tag $targetParentTag )
    {
        $this->service->moveSubtree( $tag, $targetParentTag );
        $this->signalDispatcher->emit(
            new MoveSubtreeSignal(
                array(
                    "sourceTagId" => $tag->id,
                    "targetParentTagId" => $targetParentTag->id
                )
            )
        );
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
