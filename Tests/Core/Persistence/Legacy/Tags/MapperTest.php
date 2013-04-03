<?php

namespace Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Tags;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper;

/**
 * Test case for Tags mapper
 */
class MapperTest extends TestCase
{
    /**
     * Tags data from the database
     *
     * @var array
     */
    protected $tagRow = array(
        "id" => 42,
        "parent_id" => 21,
        "main_tag_id" => 0,
        "keyword" => "Croatia",
        "depth" => 3,
        "path_string" => "/1/21/42/",
        "modified" => 1234567,
        "remote_id" => "123456abcdef"
    );

    /**
     * Expected Tag object properties values
     *
     * @var array
     */
    protected $tagValues = array(
        "id" => 42,
        "parentTagId" => 21,
        "mainTagId" => 0,
        "keyword" => "Croatia",
        "depth" => 3,
        "pathString" => "/1/21/42/",
        "modificationDate" => 1234567,
        "remoteId" => "123456abcdef"
    );

    /**
     * Expected Tag CreateStruct object properties values
     *
     * @var array
     */
    protected $tagCreateStructValues = array(
        "parentTagId" => 21,
        "keyword" => "Croatia"
    );

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper::createTagFromRow
     */
    public function testCreateTagFromRow()
    {
        $mapper = new Mapper();

        $tag = $mapper->createTagFromRow(
            $this->tagRow
        );

        $this->assertInstanceOf(
            "Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag",
            $tag
        );

        $this->assertPropertiesCorrect(
            $this->tagValues,
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper::createTagFromRow
     */
    public function testCreateTagFromRowWithPrefix()
    {
        $prefix = "some_prefix_";

        $data = array();
        foreach ( $this->tagRow as $key => $val )
        {
            $data[$prefix . $key] = $val;
        }

        $mapper = new Mapper();

        $tag = $mapper->createTagFromRow( $data, $prefix );

        $this->assertInstanceOf(
            "Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag",
            $tag
        );

        $this->assertPropertiesCorrect(
            $this->tagValues,
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper::createTagsFromRows
     */
    public function testCreateTagsFromRows()
    {
        $inputRows = array();
        for ( $i = 0; $i < 3; $i++ )
        {
            $row = $this->tagRow;
            $row["id"] += $i;
            $inputRows[] = $row;
        }

        $mapper = new Mapper();

        $tags = $mapper->createTagsFromRows( $inputRows );

        $this->assertCount( 3, $tags );

        $i = 0;
        foreach ( $tags as $tag )
        {
            $this->assertInstanceOf(
                "Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag",
                $tag
            );

            $this->assertPropertiesCorrect(
                array( "id" => $this->tagValues["id"] + $i ) + $this->tagValues,
                $tag
            );

            $i++;
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper::createTagsFromRows
     */
    public function testCreateTagsFromRowsWithPrefix()
    {
        $prefix = "some_prefix_";

        $data = array();
        foreach ( $this->tagRow as $key => $val )
        {
            $data[$prefix . $key] = $val;
        }

        $inputRows = array();
        for ( $i = 0; $i < 3; $i++ )
        {
            $row = $data;
            $row[$prefix . "id"] += $i;
            $inputRows[] = $row;
        }

        $mapper = new Mapper();

        $tags = $mapper->createTagsFromRows( $inputRows, $prefix );

        $this->assertCount( 3, $tags );

        $i = 0;
        foreach ( $tags as $tag )
        {
            $this->assertInstanceOf(
                "Netgen\\TagsBundle\\SPI\\Persistence\\Tags\\Tag",
                $tag
            );

            $this->assertPropertiesCorrect(
                array( "id" => $this->tagValues["id"] + $i ) + $this->tagValues,
                $tag
            );

            $i++;
        }
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper::getTagCreateStruct
     */
    public function testGetTagCreateStruct()
    {
        $mapper = new Mapper();

        $createStruct = $mapper->getTagCreateStruct(
            $this->tagRow
        );

        $this->assertNotEquals( $this->tagRow["remote_id"], $createStruct->remoteId );
        $this->assertPropertiesCorrect(
            $this->tagCreateStructValues,
            $createStruct
        );
    }
}
