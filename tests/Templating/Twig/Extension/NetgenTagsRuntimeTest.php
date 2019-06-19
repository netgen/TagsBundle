<?php

namespace Netgen\TagsBundle\Tests\Templating\Twig\Extension;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\LanguageService;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\Repository\TagsService;
use Netgen\TagsBundle\Templating\Twig\Extension\NetgenTagsRuntime;
use PHPUnit\Framework\TestCase;

class NetgenTagsRuntimeTest extends TestCase
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
    private $translationHelper;

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

        $this->translationHelper = $this->getMockBuilder(TranslationHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTranslatedByMethod'])
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
            $this->translationHelper,
            $this->languageService,
            $this->contentTypeService
        );

        $this->tag = new Tag();
        $this->contentType = new ContentType(['names' => ['eng-GB' => 'Translated name']]);
    }

    public function testInstanceOfTwigExtension(): void
    {
        self::assertInstanceOf(NetgenTagsRuntime::class, $this->runtime);
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

        $this->translationHelper->expects(self::once())
            ->method('getTranslatedByMethod')
            ->with($this->tag)
            ->willReturn($translated);

        self::assertSame($translated, $this->runtime->getTagKeyword(1));
    }

    public function testGetTagKeywordWithTagArgument(): void
    {
        $translated = 'translated';

        $this->tagsService->expects(self::never())
            ->method('loadTag');

        $this->translationHelper->expects(self::once())
            ->method('getTranslatedByMethod')
            ->with($this->tag)
            ->willReturn($translated);

        self::assertSame($translated, $this->runtime->getTagKeyword($this->tag));
    }

    public function testGetLanguageName(): void
    {
        $language = new Language(
            [
                'id' => 123,
                'languageCode' => 'eng-EU',
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
        $contentType = 'content_type';

        $this->contentTypeService->expects(self::once())
            ->method('loadContentType')
            ->with($contentType)
            ->willThrowException(new NotFoundException($contentType, $contentType));

        self::assertEmpty($this->runtime->getContentTypeName('content_type'));
    }

    public function testGetContentTypeNameWithNonContentTypeAsArgument(): void
    {
        $contentType = 'content_type';

        $this->contentTypeService->expects(self::once())
            ->method('loadContentType')
            ->with($contentType)
            ->willReturn($this->contentType);

        self::assertSame('Translated name', $this->runtime->getContentTypeName('content_type'));
    }

    public function testGetContentTypeNameWithContentTypeAsArgument(): void
    {
        $this->contentTypeService->expects(self::never())
            ->method('loadContentType');

        self::assertSame('Translated name', $this->runtime->getContentTypeName($this->contentType));
    }
}
