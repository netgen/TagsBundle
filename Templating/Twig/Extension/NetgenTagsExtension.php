<?php

namespace Netgen\TagsBundle\Templating\Twig\Extension;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
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
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     */
    public function __construct(TagsService $tagsService, TranslationHelper $translationHelper)
    {
        $this->tagsService = $tagsService;
        $this->translationHelper = $translationHelper;
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
}
