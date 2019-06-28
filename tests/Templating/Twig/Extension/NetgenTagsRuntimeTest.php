<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Templating\Twig\Extension;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\LanguageService;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\Repository\TagsService;
use Netgen\TagsBundle\Templating\Twig\Extension\NetgenTagsRuntime;
use PHPUnit\Framework\TestCase;

final class NetgenTagsRuntimeTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\Templating\Twig\Extension\NetgenTagsRuntime
     */
    private $runtime;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $tagsService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $languageService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $contentTypeService;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    /**
     * @var \eZ\Publish\Core\Repository\Values\ContentType\ContentType
     */
    private $contentType;

    protected function setUp(): void
    {
        $this->tagsService = $this->getMockBuilder(TagsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadTag'])
            ->getMock();

        $this->languageService = $this->getMockBuilder(LanguageService::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadLanguage'])
            ->getMock();

        $this->contentTypeService = $this->getMockBuilder(ContentTypeService::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadContentType'])
            ->getMock();

        $this->runtime = new NetgenTagsRuntime(
            $this->tagsService,
            $this->languageService,
            $this->contentTypeService
        );

        $this->tag = new Tag(
            [
                'keywords' => [
                    'eng-GB' => 'default',
                    'cro-HR' => 'translated',
                ],
                'mainLanguageCode' => 'eng-GB',
                'prioritizedLanguageCode' => 'cro-HR',
            ]
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
            ]
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
