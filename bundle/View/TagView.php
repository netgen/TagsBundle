<?php

namespace Netgen\TagsBundle\View;

use eZ\Publish\Core\MVC\Symfony\View\BaseView;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class TagView extends BaseView implements View, TagValueView, CachableView
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected $tag;

    /**
     * Sets the tag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     */
    public function setTag(Tag $tag)
    {
        $this->tag = $tag;
    }

    /**
     * Returns the tag.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Returns internal parameters that will be added to the ones returned by getParameter().
     *
     * @return array
     */
    protected function getInternalParameters()
    {
        return array('tag' => $this->tag);
    }
}
