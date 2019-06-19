<?php

namespace Netgen\TagsBundle\Matcher\Tag\Id;

use eZ\Publish\Core\MVC\Symfony\View\View;
use Netgen\TagsBundle\Matcher\Tag\MultipleValued;
use Netgen\TagsBundle\View\TagValueView;

class ParentTag extends MultipleValued
{
    public function match(View $view): bool
    {
        if (!$view instanceof TagValueView) {
            return false;
        }

        return isset($this->values[$view->getTag()->parentTagId]);
    }
}
