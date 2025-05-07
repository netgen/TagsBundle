<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use Ibexa\Contracts\Core\Repository\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use UnexpectedValueException;

use function sprintf;

final class BeforeMoveSubtreeEvent extends BeforeEvent
{
    private Tag $movedTag;

    public function __construct(private Tag $tag, private ?Tag $parentTag = null) {}

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
        $this->movedTag
            ?? throw new UnexpectedValueException(
                sprintf(
                    'Return value is not set or not a type of %s. Check with hasMovedTag() or set it with setMovedTag() before you call the getter.',
                    Tag::class,
                ),
            );

        return $this->movedTag;
    }

    public function setMovedTag(Tag $movedTag): void
    {
        $this->movedTag = $movedTag;
    }

    public function hasMovedTag(): bool
    {
        return isset($this->movedTag);
    }
}
