<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Event\Tags;

use Netgen\TagsBundle\API\Repository\Events\Tags\CopySubtreeEvent as CopySubtreeEventInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Contracts\EventDispatcher\Event;

final class CopySubtreeEvent extends Event implements CopySubtreeEventInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $copiedTag;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag|null
     */
    private $parentTag;

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
