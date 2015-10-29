<?php

namespace Netgen\TagsBundle\Matcher\Tag\Id;

use eZ\Publish\Core\MVC\Symfony\View\View;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Matcher\Tag\MultipleValued;
use Netgen\TagsBundle\View\TagValueView;

class ParentRemote extends MultipleValued
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

        $tag = $view->getTag();

        $parentTag = $this->tagsService->sudo(
            function (TagsService $tagsService) use ($tag) {
                return $tagsService->loadTag($tag->parentTagId);
            }
        );

        return isset($this->values[$parentTag->remoteId]);
    }
}
