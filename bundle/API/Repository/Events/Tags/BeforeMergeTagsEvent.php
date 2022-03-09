<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use Ibexa\Contracts\Core\Repository\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class BeforeMergeTagsEvent extends BeforeEvent
{
    private Tag $tag;

    private Tag $targetTag;

    public function __construct(Tag $tag, Tag $targetTag)
    {
        $this->tag = $tag;
        $this->targetTag = $targetTag;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function getTargetTag(): Tag
    {
        return $this->targetTag;
    }
}
