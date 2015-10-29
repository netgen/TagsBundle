<?php

namespace Netgen\TagsBundle\View;

interface TagValueView
{
    /**
     * Returns the tag.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function getTag();
}
