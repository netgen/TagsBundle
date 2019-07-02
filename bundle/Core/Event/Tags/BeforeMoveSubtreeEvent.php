<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Event\Tags;

use eZ\Publish\Core\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use UnexpectedValueException;

final class BeforeMoveSubtreeEvent extends BeforeEvent
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag|null
     */
    private $parentTag;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag|null
     */
    private $movedTag;

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

    public function getMovedTag(): Tag
    {
        if (!$this->hasMovedTag()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check with hasMovedTag() or set it with setMovedTag() before you call the getter.', Tag::class));
        }

        return $this->movedTag;
    }

    public function setMovedTag(?Tag $movedTag): void
    {
        $this->movedTag = $movedTag;
    }

    public function hasMovedTag(): bool
    {
        return $this->movedTag instanceof Tag;
    }
}
