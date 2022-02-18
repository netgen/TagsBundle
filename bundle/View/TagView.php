<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\View;

use Ibexa\Core\MVC\Symfony\View\BaseView;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class TagView extends BaseView implements TagValueView, CacheableView
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    /**
     * @var bool
     */
    private $isCacheEnabled = true;

    public function setTag(Tag $tag): void
    {
        $this->tag = $tag;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function setCacheEnabled($cacheEnabled): void
    {
        $this->isCacheEnabled = (bool) $cacheEnabled;
    }

    public function isCacheEnabled(): bool
    {
        return $this->isCacheEnabled;
    }

    protected function getInternalParameters(): array
    {
        return ['tag' => $this->tag];
    }
}
