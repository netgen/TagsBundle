<?php

namespace EzSystems\TagsBundle\Tests\Core\Persistence\Legacy\Tags;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use EzSystems\TagsBundle\Core\Persistence\Legacy\Tags\Handler;
use EzSystems\TagsBundle\SPI\Persistence\Tags\Tag;

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
}
