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
    protected $runtime;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tagsService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $languageService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeService;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected $tag;

    /**
     * @var \eZ\Publish\Core\Repository\Values\ContentType\ContentType
     */
    protected $contentType;

    public function setUp()
    {
        $this->tagsService = $this->getMockBuilder(TagsService::class)
            ->disableOriginalConstructor()
            ->setMethods(array('loadTag'))
            ->getMock();

        $this->translationHelper = $this->getMockBuilder(TranslationHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getTranslatedByMethod'))
            ->getMock();

        $this->languageService = $this->getMockBuilder(LanguageService::class)
            ->disableOriginalConstructor()
            ->setMethods(array('loadLanguage'))
            ->getMock();

        $this->contentTypeService = $this->getMockBuilder(ContentTypeService::class)
            ->disableOriginalConstructor()
            ->setMethods(array('loadContentType'))
            ->getMock();

        $this->runtime = new NetgenTagsRuntime(
            $this->tagsService,
            $this->translationHelper,
            $this->languageService,
            $this->contentTypeService
        );

        $this->tag = new Tag();
        $this->contentType = new ContentType(array('names' => array('eng-GB' => 'Translated name')));
    }

    public function testInstanceOfTwigExtension()
    {
        $this->assertInstanceOf(NetgenTagsRuntime::class, $this->runtime);
    }

    public function testGetTagKeywordWithNotFoundException()
    {
        $this->tagsService->expects($this->once())
            ->method('loadTag')
            ->willThrowException(new NotFoundException('tag', 'tag'));

        $this->assertEmpty($this->runtime->getTagKeyword(1));
    }

    public function testGetTagKeywordWithNonTagArgument()
    {
        $translated = 'translated';

        $this->tagsService->expects($this->once())
            ->method('loadTag')
            ->willReturn($this->tag);

        $this->translationHelper->expects($this->once())
            ->method('getTranslatedByMethod')
            ->with($this->tag)
            ->willReturn($translated);

        $this->assertEquals($translated, $this->runtime->getTagKeyword(1));
    }

    public function testGetTagKeywordWithTagArgument()
    {
        $translated = 'translated';

        $this->tagsService->expects($this->never())
            ->method('loadTag');

        $this->translationHelper->expects($this->once())
            ->method('getTranslatedByMethod')
            ->with($this->tag)
            ->willReturn($translated);

        $this->assertEquals($translated, $this->runtime->getTagKeyword($this->tag));
    }

    public function testGetLanguageName()
    {
        $language = new Language(
            array(
                'id' => 123,
                'languageCode' => 'eng-EU',
                'name' => 'English',
            )
        );

        $this->languageService->expects($this->once())
            ->method('loadLanguage')
            ->with($language->languageCode)
            ->willReturn($language);

        $name = $this->runtime->getLanguageName($language->languageCode);

        $this->assertEquals($language->name, $name);
    }

    public function testGetContentTypeNameWithNotFoundException()
    {
        $contentType = 'content_type';

        $this->contentTypeService->expects($this->once())
            ->method('loadContentType')
            ->with($contentType)
            ->willThrowException(new NotFoundException($contentType, $contentType));

        $this->assertEmpty($this->runtime->getContentTypeName('content_type'));
    }

    public function testGetContentTypeNameWithNonContentTypeAsArgument()
    {
        $contentType = 'content_type';

        $this->contentTypeService->expects($this->once())
            ->method('loadContentType')
            ->with($contentType)
            ->willReturn($this->contentType);

        $this->assertEquals('Translated name', $this->runtime->getContentTypeName('content_type'));
    }

    public function testGetContentTypeNameWithContentTypeAsArgument()
    {
        $this->contentTypeService->expects($this->never())
            ->method('loadContentType');

        $this->assertEquals('Translated name', $this->runtime->getContentTypeName($this->contentType));
    }
}
