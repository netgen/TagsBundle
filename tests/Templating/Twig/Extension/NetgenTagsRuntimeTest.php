<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Templating\Twig\Extension;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\ContentTypeService;
use Ibexa\Core\Repository\LanguageService;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\Repository\TagsService;
use Netgen\TagsBundle\Templating\Twig\Extension\NetgenTagsRuntime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class NetgenTagsRuntimeTest extends TestCase
{
    private NetgenTagsRuntime $runtime;

    private MockObject&TagsService $tagsService;

    private LanguageService&MockObject $languageService;

    private ContentTypeService&MockObject $contentTypeService;

    private Tag $tag;

    private ContentType $contentType;

    protected function setUp(): void
    {
        $this->tagsService = $this->getMockBuilder(TagsService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadTag'])
            ->getMock();

        $this->languageService = $this->getMockBuilder(LanguageService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadLanguage'])
            ->getMock();

        $this->contentTypeService = $this->getMockBuilder(ContentTypeService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadContentType'])
            ->getMock();

        $this->runtime = new NetgenTagsRuntime(
            $this->tagsService,
            $this->languageService,
            $this->contentTypeService,
        );

        $this->tag = new Tag(
            [
                'keywords' => [
                    'eng-GB' => 'default',
                    'cro-HR' => 'translated',
                ],
                'mainLanguageCode' => 'eng-GB',
                'prioritizedLanguageCode' => 'cro-HR',
            ],
        );

        $this->contentType = new ContentType(['names' => ['eng-GB' => 'Translated name']]);
    }

    public function testGetTagKeywordWithNotFoundException(): void
    {
        $this->tagsService->expects(self::once())
            ->method('loadTag')
            ->willThrowException(new NotFoundException('tag', 'tag'));

        self::assertEmpty($this->runtime->getTagKeyword(1));
    }

    public function testGetTagKeywordWithNonTagArgument(): void
    {
        $translated = 'translated';

        $this->tagsService->expects(self::once())
            ->method('loadTag')
            ->willReturn($this->tag);

        self::assertSame($translated, $this->runtime->getTagKeyword(1));
    }

    public function testGetLanguageName(): void
    {
        $language = new Language(
            [
                'id' => 123,
                'languageCode' => 'eng-GB',
                'name' => 'English',
            ],
        );

        $this->languageService->expects(self::once())
            ->method('loadLanguage')
            ->with($language->languageCode)
            ->willReturn($language);

        $name = $this->runtime->getLanguageName($language->languageCode);

        self::assertSame($language->name, $name);
    }

    public function testGetContentTypeNameWithNotFoundException(): void
    {
        $this->contentTypeService->expects(self::once())
            ->method('loadContentType')
            ->with(42)
            ->willThrowException(new NotFoundException('content type', 42));

        self::assertEmpty($this->runtime->getContentTypeName(42));
    }

    public function testGetContentTypeNameWithNonContentTypeAsArgument(): void
    {
        $this->contentTypeService->expects(self::once())
            ->method('loadContentType')
            ->with(42)
            ->willReturn($this->contentType);

        self::assertSame('Translated name', $this->runtime->getContentTypeName(42));
    }

    public function testGetContentTypeNameWithContentTypeAsArgument(): void
    {
        $this->contentTypeService->expects(self::never())
            ->method('loadContentType');

        self::assertSame('Translated name', $this->runtime->getContentTypeName($this->contentType));
    }
}
