<?php

namespace Netgen\TagsBundle\Matcher\Tag;

use eZ\Publish\Core\MVC\Symfony\View\View;
use Netgen\TagsBundle\View\TagValueView;

class Depth extends MultipleValued
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

        return isset($this->values[$view->getTag()->depth]);
    }
}
