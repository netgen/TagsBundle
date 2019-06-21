<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Matcher\Tag;

use eZ\Publish\Core\MVC\Symfony\View\View;
use Netgen\TagsBundle\View\TagValueView;

final class Keyword extends MultipleValued
{
    public function match(View $view): bool
    {
        if (!$view instanceof TagValueView) {
            return false;
        }

        $keyword = $this->translationHelper->getTranslatedByMethod(
            $view->getTag(),
            'getKeyword'
        );

        return isset($this->values[$keyword]);
    }
}
