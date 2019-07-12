<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Event\Tags;

use Netgen\TagsBundle\API\Repository\Events\Tags\MoveSubtreeEvent as MoveSubtreeEventInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Contracts\EventDispatcher\Event;

final class MoveSubtreeEvent extends Event implements MoveSubtreeEventInterface
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
