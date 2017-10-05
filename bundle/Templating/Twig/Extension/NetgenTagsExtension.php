<?php

namespace Netgen\TagsBundle\Templating\Twig\Extension;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Twig_Extension;
use Twig_SimpleFunction;

class NetgenTagsExtension extends Twig_Extension
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * @var \eZ\Publish\API\Repository\LanguageService
     */
    protected $languageService;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * NetgenTagsExtension constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param \eZ\Publish\API\Repository\LanguageService $languageService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(TagsService $tagsService, TranslationHelper $translationHelper, LanguageService $languageService, ContentTypeService $contentTypeService)
    {
        $this->tagsService = $tagsService;
        $this->translationHelper = $translationHelper;
        $this->languageService = $languageService;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::class;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction(
                'netgen_tags_tag_keyword',
                array($this, 'getTagKeyword')
            ),
            new Twig_SimpleFunction(
                'netgen_tags_language_name',
                array($this, 'getLanguageName')
            ),
            new Twig_SimpleFunction(
                'netgen_tags_content_type_name',
                array($this, 'getContentTypeName')
            ),
        );
    }

    /**
     * Returns tag keyword for provided tag ID or tag object.
     *
     * @param mixed|\Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return string
     */
    public function getTagKeyword($tag)
    {
        if (!$tag instanceof Tag) {
            try {
                $tag = $this->tagsService->loadTag($tag);
            } catch (NotFoundException $e) {
                return '';
            }
        }

        return $this->translationHelper->getTranslatedByMethod($tag, 'getKeyword');
    }

    /**
     * Returns the language name for specified language code.
     *
     * @param string $languageCode
     *
     * @return string
     */
    public function getLanguageName($languageCode)
    {
        return $this->languageService->loadLanguage($languageCode)->name;
    }

    /**
     * Returns content type name for provided content type ID or content type object.
     *
     * @param mixed|\eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     *
     * @return string
     */
    public function getContentTypeName($contentType)
    {
        if (!$contentType instanceof ContentType) {
            try {
                $contentType = $this->contentTypeService->loadContentType($contentType);
            } catch (NotFoundException $e) {
                return '';
            }
        }

        return $this->translationHelper->getTranslatedByMethod($contentType, 'getName');
    }
}
