<?php

namespace EzSystems\TagsBundle\Core\Persistence\Legacy\Tags;

use EzSystems\TagsBundle\SPI\Persistence\Tags\Tag;
use EzSystems\TagsBundle\SPI\Persistence\Tags\CreateStruct;

class Mapper
{
    /**
     * Creates a tag from a $data row
     *
     * $prefix can be used to define a table prefix for the eztags table
     *
     * Optionally pass a Tag object, which will be filled with the values
     *
     * @param array $data
     * @param string $prefix
     * @param \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag $tag
     *
     * @return \EzSystems\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function createTagFromRow( array $data, $prefix = "", Tag $tag = null )
    {
        $tag = $tag ?: new Tag();

        $tag->id = (int)$data[$prefix . "id"];
        $tag->parentTagId = (int)$data[$prefix . "parent_id"];
        $tag->mainTagId = (int)$data[$prefix . "main_tag_id"];
        $tag->keyword = $data[$prefix . "keyword"];
        $tag->depth = (int)$data[$prefix . "depth"];
        $tag->pathString = $data[$prefix . "path_string"];
        $tag->modificationDate = (int)$data[$prefix . "modified"];
        $tag->remoteId = $data[$prefix . "remote_id"];

        return $tag;
    }

    /**
     * Creates Tag objects from the given $rows, optionally with key
     * $prefix
     *
     * @param array $rows
     * @param string $prefix
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location[]
     */
    public function createTagsFromRows( array $rows, $prefix = "" )
    {
        $tags = array();

        foreach ( $rows as $row )
        {
            $id = $row[$prefix . "id"];
            if ( !isset( $tags[$id] ) )
            {
                $tags[$id] = $this->createTagFromRow( $row, $prefix );
            }
        }

        return array_values( $tags );
    }
}
