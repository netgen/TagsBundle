<?php

namespace Netgen\TagsBundle\Core\Repository;

use eZ\Publish\API\Repository\Repository;
use Netgen\TagsBundle\API\Repository\TagsService as TagsServiceInterface;
use Netgen\TagsBundle\SPI\Persistence\Tags\Handler;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag as SPITag;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use DateTime;
use Exception;

class TagsService implements TagsServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \Netgen\TagsBundle\SPI\Persistence\Tags\Handler
     */
    protected $tagsHandler;

    /**
     * Constructor
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\Handler $tagsHandler
     */
    public function __construct( Repository $repository, Handler $tagsHandler )
    {
        $this->repository = $repository;
        $this->tagsHandler = $tagsHandler;
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
        if ( $this->repository->hasAccess( "tags", "read" ) !== true )
        {
            throw new UnauthorizedException( "tags", "read" );
        }

        $spiTag = $this->tagsHandler->load( $tagId );
        return $this->buildTagDomainObject( $spiTag );
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
        if ( $this->repository->hasAccess( "tags", "read" ) !== true )
        {
            throw new UnauthorizedException( "tags", "read" );
        }

        $spiTag = $this->tagsHandler->loadByRemoteId( $remoteId );
        return $this->buildTagDomainObject( $spiTag );
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
        if ( $this->repository->hasAccess( "tags", "read" ) !== true )
        {
            throw new UnauthorizedException( "tags", "read" );
        }

        $spiTags = $this->tagsHandler->loadChildren(
            $tag !== null ? $tag->id : 0,
            $offset,
            $limit
        );

        $tags = array();
        foreach ( $spiTags as $spiTag )
        {
            $tags[] = $this->buildTagDomainObject( $spiTag );
        }

        return $tags;
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
        if ( $this->repository->hasAccess( "tags", "read" ) !== true )
        {
            throw new UnauthorizedException( "tags", "read" );
        }

        return $this->tagsHandler->getChildrenCount(
            $tag !== null ? $tag->id : 0
        );
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
        if ( $this->repository->hasAccess( "tags", "read" ) !== true )
        {
            throw new UnauthorizedException( "tags", "read" );
        }

        if ( $tag->mainTagId > 0 )
        {
            throw new InvalidArgumentException( "tag", "Tag is a synonym" );
        }

        $spiTags = $this->tagsHandler->loadSynonyms( $tag->id, $offset, $limit );

        $tags = array();
        foreach ( $spiTags as $spiTag )
        {
            $tags[] = $this->buildTagDomainObject( $spiTag );
        }

        return $tags;
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
        if ( $this->repository->hasAccess( "tags", "read" ) !== true )
        {
            throw new UnauthorizedException( "tags", "read" );
        }

        if ( $tag->mainTagId > 0 )
        {
            throw new InvalidArgumentException( "tag", "Tag is a synonym" );
        }

        return $this->tagsHandler->getSynonymCount( $tag->id );
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
        if ( $this->repository->hasAccess( "tags", "read" ) !== true )
        {
            throw new UnauthorizedException( "tags", "read" );
        }

        $spiTag = $this->tagsHandler->load( $tag->id );

        $relatedContentIds = $this->tagsHandler->loadRelatedContentIds( $spiTag->id );
        if ( empty( $relatedContentIds ) )
        {
            return array();
        }

        $searchResult = $this->repository->getSearchService()->findContent(
            new Query(
                array(
                    "offset" => $offset,
                    "limit" => $limit > 0 ? $limit : PHP_INT_MAX,
                    "criterion" => new ContentId( $relatedContentIds )
                )
            )
        );

        $content = array();
        foreach ( $searchResult->searchHits as $searchHit )
        {
            $content[] = $searchHit->valueObject;
        }

        return $content;
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
        if ( $this->repository->hasAccess( "tags", "read" ) !== true )
        {
            throw new UnauthorizedException( "tags", "read" );
        }

        $spiTag = $this->tagsHandler->load( $tag->id );

        $relatedContentIds = $this->tagsHandler->loadRelatedContentIds( $spiTag->id );
        if ( empty( $relatedContentIds ) )
        {
            return 0;
        }

        $searchResult = $this->repository->getSearchService()->findContent(
            new Query(
                array(
                    "limit" => 0,
                    "criterion" => new ContentId( $relatedContentIds )
                )
            )
        );

        return $searchResult->totalCount;
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
        if ( $this->repository->hasAccess( "tags", "add" ) !== true )
        {
            throw new UnauthorizedException( "tags", "add" );
        }

        if ( empty( $tagCreateStruct->keyword ) || !is_string( $tagCreateStruct->keyword ) )
        {
            throw new InvalidArgumentValue( "keyword", $tagCreateStruct->keyword, "TagCreateStruct" );
        }

        if ( $tagCreateStruct->remoteId !== null && ( empty( $tagCreateStruct->remoteId ) || !is_string( $tagCreateStruct->remoteId ) ) )
        {
            throw new InvalidArgumentValue( "remoteId", $tagCreateStruct->remoteId, "TagCreateStruct" );
        }

        // check for existence of tag with provided remote ID
        if ( $tagCreateStruct->remoteId !== null )
        {
            try
            {
                $this->tagsHandler->loadByRemoteId( $tagCreateStruct->remoteId );
                throw new InvalidArgumentException( "tagCreateStruct", "Tag with provided remote ID already exists" );
            }
            catch ( NotFoundException $e )
            {
                // Do nothing
            }
        }
        else
        {
            $tagCreateStruct->remoteId = md5( uniqid( get_class( $this ), true ) );
        }

        $createStruct = new CreateStruct();
        $createStruct->parentTagId = $tagCreateStruct->parentTagId;
        $createStruct->keyword = $tagCreateStruct->keyword;
        $createStruct->remoteId = $tagCreateStruct->remoteId;

        $this->repository->beginTransaction();
        try
        {
            $newTag = $this->tagsHandler->create( $createStruct );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildTagDomainObject( $newTag );
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
        if ( $tag->mainTagId > 0 )
        {
            if ( $this->repository->hasAccess( "tags", "edit" ) !== true )
            {
                throw new UnauthorizedException( "tags", "edit" );
            }
        }
        else
        {
            if ( $this->repository->hasAccess( "tags", "editsynonym" ) !== true )
            {
                throw new UnauthorizedException( "tags", "editsynonym" );
            }
        }

        if ( $tagUpdateStruct->keyword !== null && ( !is_string( $tagUpdateStruct->keyword ) || empty( $tagUpdateStruct->keyword ) ) )
        {
            throw new InvalidArgumentValue( "keyword", $tagUpdateStruct->keyword, "TagUpdateStruct" );
        }

        if ( $tagUpdateStruct->remoteId !== null && ( !is_string( $tagUpdateStruct->remoteId ) || empty( $tagUpdateStruct->remoteId ) ) )
        {
            throw new InvalidArgumentValue( "remoteId", $tagUpdateStruct->remoteId, "TagUpdateStruct" );
        }

        $spiTag = $this->tagsHandler->load( $tag->id );

        if ( $tagUpdateStruct->remoteId !== null )
        {
            try
            {
                $existingTag = $this->tagsHandler->loadByRemoteId( $tagUpdateStruct->remoteId );
                if ( $existingTag->id !== $spiTag->id )
                {
                    throw new InvalidArgumentException( "tagUpdateStruct", "Tag with provided remote ID already exists" );
                }
            }
            catch ( NotFoundException $e )
            {
                // Do nothing
            }
        }

        $updateStruct = new UpdateStruct();
        $updateStruct->keyword = $tagUpdateStruct->keyword !== null ? trim( $tagUpdateStruct->keyword ) : $spiTag->keyword;
        $updateStruct->remoteId = $tagUpdateStruct->remoteId !== null ? trim( $tagUpdateStruct->remoteId ) : $spiTag->remoteId;

        $this->repository->beginTransaction();
        try
        {
            $updatedTag = $this->tagsHandler->update( $updateStruct, $spiTag->id );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildTagDomainObject( $updatedTag );
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
        if ( $this->repository->hasAccess( "tags", "addsynonym" ) !== true )
        {
            throw new UnauthorizedException( "tags", "addsynonym" );
        }

        if ( empty( $keyword ) || !is_string( $keyword ) )
        {
            throw new InvalidArgumentValue( "keyword", $keyword );
        }

        $spiTag = $this->tagsHandler->load( $tag->id );

        if ( $spiTag->mainTagId > 0 )
        {
            throw new InvalidArgumentException( "tag", "Main tag is a synonym" );
        }

        $this->repository->beginTransaction();
        try
        {
            $createdSynonym = $this->tagsHandler->addSynonym( $spiTag->id, $keyword );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildTagDomainObject( $createdSynonym );
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
        if ( $this->repository->hasAccess( "tags", "makesynonym" ) !== true )
        {
            throw new UnauthorizedException( "tags", "makesynonym" );
        }

        $spiTag = $this->tagsHandler->load( $tag->id );
        $spiMainTag = $this->tagsHandler->load( $mainTag->id );

        if ( $spiTag->mainTagId > 0 )
        {
            throw new InvalidArgumentException( "tag", "Source tag is a synonym" );
        }

        if ( $spiMainTag->mainTagId > 0 )
        {
            throw new InvalidArgumentException( "mainTag", "Destination tag is a synonym" );
        }

        if ( strpos( $spiMainTag->pathString, $spiTag->pathString ) === 0 )
        {
            throw new InvalidArgumentException( "mainTag", "Destination tag is a sub tag of the given tag" );
        }

        $this->repository->beginTransaction();
        try
        {
            foreach ( $this->tagsHandler->loadChildren( $spiTag->id ) as $child )
            {
                $this->tagsHandler->moveSubtree( $child->id, $spiMainTag->id );
            }

            $convertedTag = $this->tagsHandler->convertToSynonym( $spiTag->id, $spiMainTag->id );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildTagDomainObject( $convertedTag );
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
        if ( $this->repository->hasAccess( "tags", "merge" ) !== true )
        {
            throw new UnauthorizedException( "tags", "merge" );
        }

        $spiTag = $this->tagsHandler->load( $tag->id );
        $spiTargetTag = $this->tagsHandler->load( $targetTag->id );

        if ( $spiTag->mainTagId > 0 )
        {
            throw new InvalidArgumentException( "tag", "Source tag is a synonym" );
        }

        if ( $spiTargetTag->mainTagId > 0 )
        {
            throw new InvalidArgumentException( "targetTag", "Target tag is a synonym" );
        }

        if ( strpos( $spiTargetTag->pathString, $spiTag->pathString ) === 0 )
        {
            throw new InvalidArgumentException( "targetParentTag", "Target tag is a sub tag of the given tag" );
        }

        $this->repository->beginTransaction();
        try
        {
            foreach ( $this->tagsHandler->loadChildren( $spiTag->id ) as $child )
            {
                $this->tagsHandler->moveSubtree( $child->id, $spiTargetTag->id );
            }

            $this->tagsHandler->merge( $spiTag->id, $spiTargetTag->id );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
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
        if ( $this->repository->hasAccess( "tags", "read" ) !== true )
        {
            throw new UnauthorizedException( "tags", "read" );
        }

        $spiTag = $this->tagsHandler->load( $tag->id );
        $spiParentTag = $this->tagsHandler->load( $targetParentTag->id );

        if ( $spiTag->mainTagId > 0 )
        {
            throw new InvalidArgumentException( "tag", "Source tag is a synonym" );
        }

        if ( $spiParentTag->mainTagId > 0 )
        {
            throw new InvalidArgumentException( "targetParentTag", "Target parent tag is a synonym" );
        }

        if ( $tag->parentTagId == $targetParentTag->id )
        {
            throw new InvalidArgumentException( "targetParentTag", "Target parent tag is already the parent of the given tag" );
        }

        if ( strpos( $spiParentTag->pathString, $spiTag->pathString ) === 0 )
        {
            throw new InvalidArgumentException( "targetParentTag", "Target parent tag is a sub tag of the given tag" );
        }

        $this->repository->beginTransaction();
        try
        {
            $copiedTag = $this->tagsHandler->copySubtree( $spiTag->id, $spiParentTag->id );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildTagDomainObject( $copiedTag );
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
        if ( $this->repository->hasAccess( "tags", "edit" ) !== true )
        {
            throw new UnauthorizedException( "tags", "edit" );
        }

        $spiTag = $this->tagsHandler->load( $tag->id );
        $spiParentTag = $this->tagsHandler->load( $targetParentTag->id );

        if ( $spiTag->mainTagId > 0 )
        {
            throw new InvalidArgumentException( "tag", "Source tag is a synonym" );
        }

        if ( $spiParentTag->mainTagId > 0 )
        {
            throw new InvalidArgumentException( "targetParentTag", "Target parent tag is a synonym" );
        }

        if ( $tag->parentTagId == $targetParentTag->id )
        {
            throw new InvalidArgumentException( "targetParentTag", "Target parent tag is already the parent of the given tag" );
        }

        if ( strpos( $spiParentTag->pathString, $spiTag->pathString ) === 0 )
        {
            throw new InvalidArgumentException( "targetParentTag", "Target parent tag is a sub tag of the given tag" );
        }

        $this->repository->beginTransaction();
        try
        {
            $movedTag = $this->tagsHandler->moveSubtree( $spiTag->id, $spiParentTag->id );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildTagDomainObject( $movedTag );
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
        if ( $tag->mainTagId > 0 )
        {
            if ( $this->repository->hasAccess( "tags", "deletesynonym" ) !== true )
            {
                throw new UnauthorizedException( "tags", "deletesynonym" );
            }
        }
        else
        {
            if ( $this->repository->hasAccess( "tags", "delete" ) !== true )
            {
                throw new UnauthorizedException( "tags", "delete" );
            }
        }

        $this->repository->beginTransaction();
        try
        {
            $this->tagsHandler->deleteTag( $tag->id );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
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
        $tagCreateStruct = new TagCreateStruct();
        $tagCreateStruct->parentTagId = $parentTagId;
        $tagCreateStruct->keyword = $keyword;

        return $tagCreateStruct;
    }

    /**
     * Instantiates a new tag update struct
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct
     */
    public function newTagUpdateStruct()
    {
        return new TagUpdateStruct();
    }

    protected function buildTagDomainObject( SPITag $spiTag )
    {
        $modificationDate = new DateTime();
        $modificationDate->setTimestamp( $spiTag->modificationDate );

        return new Tag(
            array(
                "id" => $spiTag->id,
                "parentTagId" => $spiTag->parentTagId,
                "mainTagId" => $spiTag->mainTagId,
                "keyword" => $spiTag->keyword,
                "depth" => $spiTag->depth,
                "pathString" => $spiTag->pathString,
                "modificationDate" => $modificationDate,
                "remoteId" => $spiTag->remoteId
            )
        );
    }
}
