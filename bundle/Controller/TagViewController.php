<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Controller;

use Ibexa\Bundle\Core\Controller;
use Netgen\TagsBundle\View\TagView;

final class TagViewController extends Controller
{
    /**
     * Action for rendering a tag view.
     */
    public function viewAction(TagView $view): TagView
    {
        return $view;
    }
}
