<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use Ibexa\Contracts\Core\Repository\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class CopySubtreeEvent extends AfterEvent
{
    private Tag $tag;

    private Tag $copiedTag;

    private ?Tag $parentTag;

    public function __construct(Tag $tag, Tag $copiedTag, ?Tag $parentTag = null)
    {
        $this->tag = $tag;
        $this->copiedTag = $copiedTag;
        $this->parentTag = $parentTag;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function getCopiedTag(): Tag
    {
        return $this->copiedTag;
    }

    public function getParentTag(): ?Tag
    {
        return $this->parentTag;
    }
}
