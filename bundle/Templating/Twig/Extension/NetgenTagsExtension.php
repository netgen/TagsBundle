<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Templating\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NetgenTagsExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
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
        ];
    }
}
