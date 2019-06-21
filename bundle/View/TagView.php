<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\View;

use eZ\Publish\Core\MVC\Symfony\View\BaseView;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class TagView extends BaseView implements TagValueView, CacheableView
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    public function setTag(Tag $tag): void
    {
        $this->tag = $tag;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    protected function getInternalParameters(): array
    {
        return ['tag' => $this->tag];
    }
}
