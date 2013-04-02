<?php

namespace EzSystems\TagsBundle\Tests\Core\Repository\Service\Integration;

use eZ\Publish\Core\Repository\Tests\Service\Integration\Base as BaseServiceTest;
use EzSystems\TagsBundle\API\Repository\Values\Tags\Tag;

use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;
use eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException;

use DateTime;

/**
 * Test case for Tags Service
 */
abstract class TagsBase extends BaseServiceTest
{
    /**
     * @var \EzSystems\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * Test a new class and default values on properties
     * @covers \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag::__construct
     */
    public function testNewClass()
    {
        $tag = new Tag();

        $this->assertPropertiesCorrect(
            array(
                "id" => null,
                "parentTagId" => null,
                "mainTagId" => null,
                "keyword" => null,
                "depth" => null,
                "pathString" => null,
                "modificationDate" => null,
                "remoteId" => null
            ),
            $tag
        );
    }

    /**
     * Test retrieving missing property
     * @covers \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag::__get
     */
    public function testMissingProperty()
    {
        try
        {
            $tag = new Tag();
            $value = $tag->notDefined;
            $this->fail( "Succeeded getting non existing property" );
        }
        catch ( PropertyNotFoundException $e )
        {
        }
    }

    /**
     * Test setting read only property
     * @covers \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag::__set
     */
    public function testReadOnlyProperty()
    {
        try
        {
            $tag = new Tag();
            $tag->id = 42;
            $this->fail( "Succeeded setting read only property" );
        }
        catch ( PropertyReadOnlyException $e )
        {
        }
    }

    /**
     * Test if property exists
     * @covers \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag::__isset
     */
    public function testIsPropertySet()
    {
        $tag = new Tag();
        $value = isset( $tag->notDefined );
        $this->assertEquals( false, $value );

        $value = isset( $tag->id );
        $this->assertEquals( true, $value );
    }

    /**
     * Test unsetting a property
     * @covers \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag::__unset
     */
    public function testUnsetProperty()
    {
        $tag = new Tag( array( "id" => 2 ) );
        try
        {
            unset( $tag->id );
            $this->fail( "Unsetting read-only property succeeded" );
        }
        catch ( PropertyReadOnlyException $e )
        {
        }
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Repository\TagsService::loadTag
     */
    public function testLoadTag()
    {
        $tag = $this->tagsService->loadTag( 40 );

        $this->assertInstanceOf( "\\EzSystems\\TagsBundle\\API\\Repository\\Values\\Tags\\Tag", $tag );

        $this->assertPropertiesCorrect(
            array(
                "id" => 40,
                "parentTagId" => 7,
                "mainTagId" => 0,
                "keyword" => "eztags",
                "depth" => 3,
                "pathString" => "/8/7/40/",
                "modificationDate" => $this->getDateTime( 1308153110 ),
                "remoteId" => "182be0c5cdcd5072bb1864cdee4d3d6e"
            ),
            $tag
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @covers \EzSystems\TagsBundle\Core\Repository\TagsService::loadTag
     */
    public function testLoadTagThrowsNotFoundException()
    {
        $this->tagsService->loadTag( PHP_INT_MAX );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \EzSystems\TagsBundle\Core\Repository\TagsService::loadTag
     */
    public function testLoadTagThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser( $this->getStubbedUser( 10 ) );
        $this->tagsService->loadTag( 40 );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Repository\TagsService::loadTagByRemoteId
     */
    public function testLoadTagByRemoteId()
    {
        $tag = $this->tagsService->loadTagByRemoteId( "182be0c5cdcd5072bb1864cdee4d3d6e" );

        $this->assertInstanceOf( "\\EzSystems\\TagsBundle\\API\\Repository\\Values\\Tags\\Tag", $tag );

        $this->assertPropertiesCorrect(
            array(
                "id" => 40,
                "parentTagId" => 7,
                "mainTagId" => 0,
                "keyword" => "eztags",
                "depth" => 3,
                "pathString" => "/8/7/40/",
                "modificationDate" => $this->getDateTime( 1308153110 ),
                "remoteId" => "182be0c5cdcd5072bb1864cdee4d3d6e"
            ),
            $tag
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @covers \EzSystems\TagsBundle\Core\Repository\TagsService::loadTagByRemoteId
     */
    public function testLoadTagByRemoteIdThrowsNotFoundException()
    {
        $this->tagsService->loadTagByRemoteId( "Non-existing remote ID" );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @covers \EzSystems\TagsBundle\Core\Repository\TagsService::loadTagByRemoteId
     */
    public function testLoadTagByRemoteIdThrowsUnauthorizedException()
    {
        $this->repository->setCurrentUser( $this->getStubbedUser( 10 ) );
        $this->tagsService->loadTagByRemoteId( "182be0c5cdcd5072bb1864cdee4d3d6e" );
    }

    /**
     * Creates and returns a \DateTime object with received timestamp
     *
     * @param int $timestamp
     *
     * @return \DateTime
     */
    protected function getDateTime( $timestamp = null )
    {
        $timestamp = $timestamp ?: time();

        $dateTime = new DateTime();
        $dateTime->setTimestamp( $timestamp );

        return $dateTime;
    }
}
