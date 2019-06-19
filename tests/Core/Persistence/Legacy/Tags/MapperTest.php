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
     * Tags data from the database.
     *
     * @var array
     */
    private static $tagRow = [
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
    ];

    /**
     * Tags list data from the database.
     *
     * @var array
     */
    private static $tagListRow = [
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
    ];

    /**
     * Expected Tag object properties values.
     *
     * @var array
     */
    private static $tagValues = [
        'id' => 42,
        'parentTagId' => 21,
        'mainTagId' => 0,
        'depth' => 3,
        'pathString' => '/1/21/42/',
        'modificationDate' => 1234567,
        'remoteId' => '123456abcdef',
        'alwaysAvailable' => true,
        'mainLanguageCode' => 'eng-GB',
        'languageIds' => [8],
    ];

    /**
     * Expected Tag object properties values.
     *
     * @var array
     */
    private static $tagListValues = [
        'id' => 42,
        'parentTagId' => 21,
        'mainTagId' => 0,
        'keywords' => ['eng-GB' => 'Croatia'],
        'depth' => 3,
        'pathString' => '/1/21/42/',
        'modificationDate' => 1234567,
        'remoteId' => '123456abcdef',
        'alwaysAvailable' => true,
        'mainLanguageCode' => 'eng-GB',
        'languageIds' => [8],
    ];
    /**
     * @var \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper
     */
    private $tagsMapper;

    protected function setUp(): void
    {
        $this->tagsMapper = $this->getMapper();
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper::__construct
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper::createTagInfoFromRow
     */
    public function testCreateTagInfoFromRow(): void
    {
        $tag = $this->tagsMapper->createTagInfoFromRow(
            self::$tagRow
        );

        self::assertInstanceOf(
            TagInfo::class,
            $tag
        );

        $this->assertPropertiesCorrect(
            self::$tagValues,
            $tag
        );
    }

    /**
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper::__construct
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper::extractTagListFromRows
     */
    public function testExtractTagListFromRows(): void
    {
        $inputRows = [];
        for ($i = 0; $i < 3; ++$i) {
            $row = self::$tagListRow;
            $row['eztags_id'] += $i;
            $inputRows[] = $row;
        }

        $tags = $this->tagsMapper->extractTagListFromRows($inputRows);

        self::assertCount(3, $tags);

        $i = 0;
        foreach ($tags as $tag) {
            self::assertInstanceOf(
                Tag::class,
                $tag
            );

            $this->assertPropertiesCorrect(
                ['id' => self::$tagListValues['id'] + $i] + self::$tagListValues,
                $tag
            );

            ++$i;
        }
    }

    /**
     * Returns mapper instance for testing.
     */
    private function getMapper(): Mapper
    {
        $languageHandlerMock = (new LanguageHandlerMock())($this);

        return new Mapper(
            $languageHandlerMock,
            new MaskGenerator($languageHandlerMock)
        );
    }
}
