<?php

namespace Netgen\TagsBundle\Matcher\Tag\Id;

use eZ\Publish\Core\MVC\Symfony\View\View;
use Netgen\TagsBundle\Matcher\Tag\MultipleValued;
use Netgen\TagsBundle\View\TagValueView;

class Tag extends MultipleValued
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

        return isset($this->values[$view->getTag()->id]);
    }
}
