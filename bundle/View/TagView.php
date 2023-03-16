<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\View;

use Ibexa\Core\MVC\Symfony\View\BaseView;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class TagView extends BaseView implements TagValueView, CacheableView
{
    private Tag $tag;

    private bool $isCacheEnabled = true;

    public function setTag(Tag $tag): void
    {
        $this->tag = $tag;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function setCacheEnabled(bool $cacheEnabled): void
    {
        $this->isCacheEnabled = $cacheEnabled;
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
