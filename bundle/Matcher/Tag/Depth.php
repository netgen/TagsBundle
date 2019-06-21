<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Matcher\Tag;

use eZ\Publish\Core\MVC\Symfony\View\View;
use Netgen\TagsBundle\View\TagValueView;

final class Depth extends MultipleValued
{
    public function match(View $view): bool
    {
        if (!$view instanceof TagValueView) {
            return false;
        }

        return isset($this->values[$view->getTag()->depth]);
    }
}
