<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Tags;

use Ibexa\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use Ibexa\Tests\Core\Persistence\Legacy\TestCase;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag;
use Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Content\LanguageHandlerMock;

final class MapperTest extends TestCase
{
    /**
     * Tags data from the database.
     *
     * @var array<string, mixed>
     */
    private static array $tagRow = [
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
        'is_hidden' => 0,
        'is_invisible' => 0,
    ];

    /**
     * Tags list data from the database.
     *
     * @var array<string, mixed>
     */
    private static array $tagListRow = [
        'id' => 42,
        'parent_id' => 21,
        'main_tag_id' => 0,
        'depth' => 3,
        'path_string' => '/1/21/42/',
        'modified' => 1234567,
        'remote_id' => '123456abcdef',
        'main_language_id' => 8,
        'language_mask' => 9,
        'keyword' => 'Croatia',
        'locale' => 'eng-GB',
        'is_hidden' => 0,
        'is_invisible' => 0,
    ];

    /**
     * Expected Tag object properties values.
     *
     * @var array<string, mixed>
     */
    private static array $tagValues = [
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
        'isHidden' => false,
        'isInvisible' => false,
    ];

    /**
     * Expected Tag object properties values.
     *
     * @var array<string, mixed>
     */
    private static array $tagListValues = [
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
        'isHidden' => false,
        'isInvisible' => false,
    ];

    private Mapper $tagsMapper;

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
            self::$tagRow,
        );

        $this->assertPropertiesCorrect(
            self::$tagValues,
            $tag,
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
            $row['id'] += $i;
            $inputRows[] = $row;
        }

        $tags = $this->tagsMapper->extractTagListFromRows($inputRows);

        self::assertCount(3, $tags);

        $i = 0;
        foreach ($tags as $tag) {
            self::assertInstanceOf(
                Tag::class,
                $tag,
            );

            $this->assertPropertiesCorrect(
                ['id' => self::$tagListValues['id'] + $i] + self::$tagListValues,
                $tag,
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
            new MaskGenerator($languageHandlerMock),
        );
    }
}
