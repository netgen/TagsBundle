<?php

namespace Netgen\TagsBundle\Templating\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NetgenTagsExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return array(
            new TwigFunction(
                'netgen_tags_tag_keyword',
                array(NetgenTagsRuntime::class, 'getTagKeyword')
            ),
            new TwigFunction(
                'netgen_tags_language_name',
                array(NetgenTagsRuntime::class, 'getLanguageName')
            ),
            new TwigFunction(
                'netgen_tags_content_type_name',
                array(NetgenTagsRuntime::class, 'getContentTypeName')
            ),
        );
    }
}
