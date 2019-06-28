<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Matcher\Tag;

use eZ\Publish\Core\MVC\Symfony\View\View;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\View\TagValueView;

final class ParentKeyword extends MultipleValued
{
    public function match(View $view): bool
    {
        if (!$view instanceof TagValueView) {
            return false;
        }

        $tag = $view->getTag();

        $parentTag = $this->tagsService->sudo(
            static function (TagsService $tagsService) use ($tag): Tag {
                return $tagsService->loadTag($tag->parentTagId);
            }
        );

        return isset($this->values[$parentTag->getKeyword()]);
    }
}
