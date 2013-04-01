<?php

namespace EzSystems\TagsBundle\Tests\Core\Persistence\Legacy\Tags;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Handler;
use EzSystems\TagsBundle\SPI\Persistence\Tags\Tag;
use EzSystems\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use EzSystems\TagsBundle\SPI\Persistence\Tags\UpdateStruct;

/**
 * Test case for Tags Handler
 */
class TagsHandlerTest extends TestCase
{
    /**
     * Mocked tags gateway instance
     *
     * @var \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Gateway
     */
    protected $gateway;

    /**
     * Mocked tags mapper instance
     *
     * @var \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Mapper
     */
    protected $mapper;

    protected function getTagsHandler()
    {
        return new Handler(
            $this->gateway = $this->getMock( "EzSystems\\TagsBundle\\Core\\Persistence\\Legacy\\Tags\\Gateway" ),
            $this->mapper = $this->getMock( "EzSystems\\TagsBundle\\Core\\Persistence\\Legacy\\Tags\\Mapper" )
        );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Handler::load
     */
    public function testLoad()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects( $this->once() )
            ->method( "getBasicTagData" )
            ->with( 42 )
            ->will(
                $this->returnValue(
                    array(
                        "id" => 42,
                    )
                )
            );

        $this->mapper
            ->expects( $this->once() )
            ->method( "createTagFromRow" )
            ->with( array( "id" => 42 ) )
            ->will( $this->returnValue( new Tag( array( "id" => 42 ) ) ) );

        $tag = $handler->load( 42 );

        $this->assertInstanceOf(
            "EzSystems\\TagsBundle\\SPI\\Persistence\\Tags\\Tag",
            $tag
        );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Handler::loadByRemoteId
     */
    public function testLoadByRemoteId()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects( $this->once() )
            ->method( "getBasicTagDataByRemoteId" )
            ->with( "abcdef" )
            ->will(
                $this->returnValue(
                    array(
                        "remote_id" => "abcdef",
                    )
                )
            );

        $this->mapper
            ->expects( $this->once() )
            ->method( "createTagFromRow" )
            ->with( array( "remote_id" => "abcdef" ) )
            ->will( $this->returnValue( new Tag( array( "remoteId" => "abcdef" ) ) ) );

        $tag = $handler->loadByRemoteId( "abcdef" );

        $this->assertInstanceOf(
            "EzSystems\\TagsBundle\\SPI\\Persistence\\Tags\\Tag",
            $tag
        );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Handler::create
     */
    public function testCreate()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects( $this->once() )
            ->method( "getBasicTagData" )
            ->with( 21 )
            ->will(
                $this->returnValue(
                    array(
                        "id" => 21,
                        "depth" => 2,
                        "path_string" => "/1/2/",
                    )
                )
            );

        $this->gateway
            ->expects( $this->once() )
            ->method( "create" )
            ->with(
                new CreateStruct(
                    array(
                        "parentTagId" => 21,
                        "keyword" => "New tag",
                        "remoteId" => "123456abcdef"
                    )
                ),
                array(
                    "id" => 21,
                    "depth" => 2,
                    "path_string" => "/1/2/",
                )
            )
            ->will(
                $this->returnValue(
                    new Tag(
                        array(
                            "id" => 95,
                            "parentTagId" => 21,
                            "mainTagId" => 0,
                            "keyword" => "New tag",
                            "depth" => 3,
                            "pathString" => "/1/2/95/",
                            "remoteId" => "123456abcdef"
                        )
                    )
                )
            );

        $tag = $handler->create(
            new CreateStruct(
                array(
                    "parentTagId" => 21,
                    "keyword" => "New tag",
                    "remoteId" => "123456abcdef"
                )
            )
        );

        $this->assertInstanceOf(
            "EzSystems\\TagsBundle\\SPI\\Persistence\\Tags\\Tag",
            $tag
        );

        $this->assertPropertiesCorrect(
            array(
                "id" => 95,
                "parentTagId" => 21,
                "keyword" => "New tag",
                "remoteId" => "123456abcdef"
            ),
            $tag
        );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Handler::update
     */
    public function testUpdate()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects( $this->once() )
            ->method( "update" )
            ->with(
                new UpdateStruct(
                    array(
                        "keyword" => "Updated tag",
                        "remoteId" => "123456abcdef"
                    )
                ),
                40
            );

        $this->gateway
            ->expects( $this->any() )
            ->method( "getBasicTagData" )
            ->with( 40 )
            ->will(
                $this->returnValue(
                    array(
                        "id" => 40,
                        "keyword" => "Updated tag",
                        "remote_id" => "123456abcdef"
                    )
                )
            );

        $this->mapper
            ->expects( $this->any() )
            ->method( "createTagFromRow" )
            ->with(
                array(
                    "id" => 40,
                    "keyword" => "Updated tag",
                    "remote_id" => "123456abcdef"
                )
            )
            ->will(
                $this->returnValue(
                    new Tag(
                        array(
                            "id" => 40,
                            "keyword" => "Updated tag",
                            "remoteId" => "123456abcdef"
                        )
                    )
                )
            );

        $tag = $handler->update(
            new UpdateStruct(
                array(
                    "keyword" => "Updated tag",
                    "remoteId" => "123456abcdef"
                )
            ),
            40
        );

        $this->assertInstanceOf(
            "EzSystems\\TagsBundle\\SPI\\Persistence\\Tags\\Tag",
            $tag
        );

        $this->assertPropertiesCorrect(
            array(
                "keyword" => "Updated tag",
                "remoteId" => "123456abcdef"
            ),
            $tag
        );
    }

    /**
     * @covers \EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Handler::deleteTag
     */
    public function testDeleteTag()
    {
        $handler = $this->getTagsHandler();

        $this->gateway
            ->expects( $this->once() )
            ->method( "getBasicTagData" )
            ->with( 40 )
            ->will(
                $this->returnValue(
                    array(
                        "id" => 40,
                    )
                )
            );

        $this->mapper
            ->expects( $this->once() )
            ->method( "createTagFromRow" )
            ->with( array( "id" => 40 ) )
            ->will( $this->returnValue( new Tag( array( "id" => 40 ) ) ) );

        $this->gateway
            ->expects( $this->once() )
            ->method( "deleteTag" )
            ->with( 40 );

        $handler->deleteTag( 40 );
    }
}
