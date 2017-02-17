<?php

namespace Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Tags;

use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag;
use Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo;
use Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Content\LanguageHandlerMock;

/**
 * Test case for Tags mapper.
 */
class MapperTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper
     */
    protected $tagsMapper;

    /**
     * Tags data from the database.
     *
     * @var array
     */
    protected $tagRow = array(
        'id' => 42,
        'parent_id' => 21,
        'main_tag_id' => 0,
        'keyword' => 'Croatia',
        'depth' => 3,
        'path_string' => '/1/21/42/',
        'modified' => 1234567,
        'remote_id' => '123456abcdef',
        'main_language_id' => 8,
        'language_mask' => 9,
    );

    /**
     * Tags list data from the database.
     *
     * @var array
     */
    protected $tagListRow = array(
        'eztags_id' => 42,
        'eztags_parent_id' => 21,
        'eztags_main_tag_id' => 0,
        'eztags_keyword' => 'Croatia',
        'eztags_depth' => 3,
        'eztags_path_string' => '/1/21/42/',
        'eztags_modified' => 1234567,
        'eztags_remote_id' => '123456abcdef',
        'eztags_main_language_id' => 8,
        'eztags_language_mask' => 9,
        'eztags_keyword_keyword' => 'Croatia',
        'eztags_keyword_locale' => 'eng-GB',
    );

    /**
     * Expected Tag object properties values.
     *
     * @var array
     */
    protected $tagValues = array(
        'id' => 42,
        'parentTagId' => 21,
        'mainTagId' => 0,
        'depth' => 3,
        'pathString' => '/1/21/42/',
        'modificationDate' => 1234567,
        'remoteId' => '123456abcdef',
        'alwaysAvailable' => true,
        'mainLanguageCode' => 'eng-GB',
        'languageIds' => array(8),
    );

    /**
     * Expected Tag object properties values.
     *
     * @var array
     */
    protected $tagListValues = array(
        'id' => 42,
        'parentTagId' => 21,
        'mainTagId' => 0,
        'keywords' => array('eng-GB' => 'Croatia'),
        'depth' => 3,
        'pathString' => '/1/21/42/',
        'modificationDate' => 1234567,
        'remoteId' => '123456abcdef',
        'alwaysAvailable' => true,
        'mainLanguageCode' => 'eng-GB',
        'languageIds' => array(8),
    );

    public function setUp()
    {
        $this->tagsMapper = $this->getMapper();
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper::__construct
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper::createTagInfoFromRow
     */
    public function testCreateTagInfoFromRow()
    {
        $tag = $this->tagsMapper->createTagInfoFromRow(
            $this->tagRow
        );

        $this->assertInstanceOf(
            TagInfo::class,
            $tag
        );

        $this->assertPropertiesCorrect(
            $this->tagValues,
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper::__construct
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper::extractTagListFromRows
     */
    public function testExtractTagListFromRows()
    {
        $inputRows = array();
        for ($i = 0; $i < 3; ++$i) {
            $row = $this->tagListRow;
            $row['eztags_id'] += $i;
            $inputRows[] = $row;
        }

        $tags = $this->tagsMapper->extractTagListFromRows($inputRows);

        $this->assertCount(3, $tags);

        $i = 0;
        foreach ($tags as $tag) {
            $this->assertInstanceOf(
                Tag::class,
                $tag
            );

            $this->assertPropertiesCorrect(
                array('id' => $this->tagListValues['id'] + $i) + $this->tagListValues,
                $tag
            );

            ++$i;
        }
    }

    /**
     * Returns mapper instance for testing.
     *
     * @return \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper
     */
    protected function getMapper()
    {
        return new Mapper(
            new LanguageHandlerMock(),
            new MaskGenerator(
                new LanguageHandlerMock()
            )
        );
    }
}
