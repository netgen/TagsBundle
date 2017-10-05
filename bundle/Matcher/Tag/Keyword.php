<?php

namespace Netgen\TagsBundle\Matcher\Tag;

use eZ\Publish\Core\MVC\Symfony\View\View;
use Netgen\TagsBundle\View\TagValueView;

class Keyword extends MultipleValued
{
    /**
     * Matches the $view against a set of matchers.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     *
     * @return bool
     */
    public function match(View $view)
    {
        if (!$view instanceof TagValueView) {
            return false;
        }

        $keyword = $this->translationHelper->getTranslatedByMethod(
            $view->getTag(), 'getKeyword'
        );

        return isset($this->values[$keyword]);
    }
}
