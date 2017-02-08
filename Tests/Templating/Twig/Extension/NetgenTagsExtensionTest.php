<?php

namespace Netgen\TagsBundle\Tests\Templating\Twig\Extension;

use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\ContentTypeService;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Repository\LanguageService;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use Netgen\TagsBundle\Core\Repository\TagsService;
use Netgen\TagsBundle\Templating\Twig\Extension\NetgenTagsExtension;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit_Framework_TestCase;
use Twig_Extension;
use Twig_SimpleFunction;

class NetgenTagsExtensionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Netgen\TagsBundle\Templating\Twig\Extension\NetgenTagsExtension
     */
    protected $extension;

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

        $this->extension = new NetgenTagsExtension(
            $this->tagsService,
            $this->translationHelper,
            $this->languageService,
            $this->contentTypeService
        );

        $this->tag = new Tag();
        $this->contentType = new ContentType(array('fieldDefinitions' => array()));
    }

    public function testInstanceOfTwigExtension()
    {
        $this->assertInstanceOf(Twig_Extension::class, $this->extension);
    }

    public function testGetName()
    {
        $this->assertEquals(NetgenTagsExtension::class, $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $functions = array(
            new Twig_SimpleFunction(
                'netgen_tags_tag_keyword',
                array($this->extension, 'getTagKeyword')
            ),
            new Twig_SimpleFunction(
                'netgen_tags_language_name',
                array($this->extension, 'getLanguageName')
            ),
            new Twig_SimpleFunction(
                'netgen_tags_content_type_name',
                array($this->extension, 'getContentTypeName')
            ),
        );

        $this->assertEquals($functions, $this->extension->getFunctions());
    }

    public function testGetTagKeywordWithNotFoundException()
    {
        $this->tagsService->expects($this->once())
            ->method('loadTag')
            ->willThrowException(new NotFoundException('tag', 'tag'));

        $this->assertEmpty($this->extension->getTagKeyword(1));
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

        $this->assertEquals($translated, $this->extension->getTagKeyword(1));
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

        $this->assertEquals($translated, $this->extension->getTagKeyword($this->tag));
    }

    public function testGetLanguageName()
    {
        $language = new Language(
            array(
                'id' => 123,
                'languageCode' => 'eng-EU',
                'name' => 'English'
            )
        );

        $this->languageService->expects($this->once())
            ->method('loadLanguage')
            ->with($language->languageCode)
            ->willReturn($language);

        $name = $this->extension->getLanguageName($language->languageCode);

        $this->assertEquals($language->name, $name);
    }

    public function testGetContentTypeNameWithNotFoundException()
    {
        $contentType = 'content_type';

        $this->contentTypeService->expects($this->once())
            ->method('loadContentType')
            ->with($contentType)
            ->willThrowException(new NotFoundException($contentType, $contentType));

        $this->translationHelper->expects($this->never())
            ->method('getTranslatedByMethod');

        $this->assertEmpty($this->extension->getContentTypeName('content_type'));
    }

    public function testGetContentTypeNameWithNonContentTypeAsArgument()
    {
        $contentType = 'content_type';

        $this->contentTypeService->expects($this->once())
            ->method('loadContentType')
            ->with($contentType)
            ->willReturn($this->contentType);

        $this->translationHelper->expects($this->once())
            ->method('getTranslatedByMethod')
            ->with($this->contentType, 'getName')
            ->willReturn('Translated name');

        $this->assertEquals('Translated name', $this->extension->getContentTypeName('content_type'));
    }

    public function testGetContentTypeNameWithContentTypeAsArgument()
    {
        $this->contentTypeService->expects($this->never())
            ->method('loadContentType');

        $this->translationHelper->expects($this->once())
            ->method('getTranslatedByMethod')
            ->with($this->contentType, 'getName')
            ->willReturn('Translated name');

        $this->assertEquals('Translated name', $this->extension->getContentTypeName($this->contentType));
    }
}
