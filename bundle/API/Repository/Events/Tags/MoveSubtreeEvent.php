<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use eZ\Publish\SPI\Repository\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class MoveSubtreeEvent extends AfterEvent
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag|null
     */
    private $parentTag;

    public function __construct(Tag $tag, ?Tag $parentTag = null)
    {
        $this->tag = $tag;
        $this->parentTag = $parentTag;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function getParentTag(): ?Tag
    {
        return $this->parentTag;
    }
}
