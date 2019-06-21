<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Matcher\Tag\Id;

use eZ\Publish\Core\MVC\Symfony\View\View;
use Netgen\TagsBundle\Matcher\Tag\MultipleValued;
use Netgen\TagsBundle\View\TagValueView;

class Remote extends MultipleValued
{
    public function match(View $view): bool
    {
        if (!$view instanceof TagValueView) {
            return false;
        }

        return isset($this->values[$view->getTag()->remoteId]);
    }
}
