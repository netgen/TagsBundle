<?php

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Tags;

use Netgen\TagsBundle\SPI\Persistence\Tags\Tag;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;

class Mapper
{
    /**
     * Caching language handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Constructor
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     */
    public function __construct( LanguageHandler $languageHandler )
    {
        $this->languageHandler = $languageHandler;
    }

    /**
     * Creates a tag from a $data row
     *
     * $prefix can be used to define a table prefix for the eztags table
     *
     * Optionally pass a Tag object, which will be filled with the values
     *
     * @param array $data
     * @param string $prefix
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\Tag $tag
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag
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
     * Creates Tag objects from the given $rows, optionally with key $prefix
     *
     * @param array $rows
     * @param string $prefix
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
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

    /**
     * Extracts a Tag object from $row
     *
     * @param array $rows
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function extractTagListFromRows( array $rows )
    {
        $tagList = array();
        foreach ( $rows as $row )
        {
            $tagId = (int)$row['eztags_id'];
            if ( !isset( $tagList[$tagId] ) )
            {
                $tag = new Tag();
                $tag->id = (int)$row['eztags_id'];
                $tag->parentTagId = (int)$row['eztags_parent_id'];
                $tag->mainTagId = (int)$row['eztags_main_tag_id'];
                $tag->keywords = array();
                $tag->depth = (int)$row['eztags_depth'];
                $tag->pathString = $row['eztags_path_string'];
                $tag->modificationDate = (int)$row['eztags_modified'];
                $tag->remoteId = $row['eztags_remote_id'];
                $tag->alwaysAvailable = ( (int)$row['eztags_language_mask'] & 1 ) ? true : false;
                $tag->mainLanguageCode = $this->languageHandler->load( $row['eztags_main_language_id'] )->languageCode;
                $tag->languageIds = $this->extractLanguageIdsFromMask( (int)$row['eztags_language_mask'] );
                $tagList[$tagId] = $tag;
            }

            if ( !isset( $tagList[$tagId]->keywords[$row['eztags_keyword_locale']] ) )
            {
                $tagList[$tagId]->keywords[$row['eztags_keyword_locale']] = $row['eztags_keyword_keyword'];
            }
        }

        return array_values( $tagList );
    }

    /**
     * Extracts language IDs from language mask
     *
     * @TODO Use language mask handler for this
     *
     * @param int $languageMask
     *
     * @return array
     */
    protected function extractLanguageIdsFromMask( $languageMask )
    {
        $exp = 2;
        $result = array();

        // Decomposition of $languageMask into its binary components
        while ( $exp <= $languageMask )
        {
            if ( $languageMask & $exp )
                $result[] = $exp;

            $exp *= 2;
        }

        return $result;
    }
}
