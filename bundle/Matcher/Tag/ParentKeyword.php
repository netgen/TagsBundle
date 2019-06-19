<?php

namespace Netgen\TagsBundle\Matcher\Tag;

use eZ\Publish\Core\MVC\Symfony\View\View;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\View\TagValueView;

class ParentKeyword extends MultipleValued
{
    /**
     * Matches the $view against a set of matchers.
     */
    public function match(View $view): bool
    {
        if (!$view instanceof TagValueView) {
            return false;
        }

        $tag = $view->getTag();

        $parentTag = $this->tagsService->sudo(
            static function (TagsService $tagsService) use ($tag) {
                return $tagsService->loadTag($tag->parentTagId);
            }
        );

        $keyword = $this->translationHelper->getTranslatedByMethod(
            $parentTag,
            'getKeyword'
        );

        return isset($this->values[$keyword]);
    }
}
