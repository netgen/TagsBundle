<?php

namespace Netgen\TagsBundle\Templating\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NetgenTagsExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'netgen_tags_tag_keyword',
                [NetgenTagsRuntime::class, 'getTagKeyword']
            ),
            new TwigFunction(
                'netgen_tags_language_name',
                [NetgenTagsRuntime::class, 'getLanguageName']
            ),
            new TwigFunction(
                'netgen_tags_content_type_name',
                [NetgenTagsRuntime::class, 'getContentTypeName']
            ),
            new TwigFunction(
                'netgen_tags_parent_tag',
                [NetgenTagsRuntime::class, 'getParentTag']
            ),
        ];
    }
}
