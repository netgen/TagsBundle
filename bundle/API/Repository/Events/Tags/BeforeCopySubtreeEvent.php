<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use Ibexa\Contracts\Core\Repository\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use UnexpectedValueException;

use function sprintf;

final class BeforeCopySubtreeEvent extends BeforeEvent
{
    private Tag $copiedTag;

    public function __construct(private Tag $tag, private ?Tag $parentTag = null)
    {
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function getParentTag(): ?Tag
    {
        return $this->parentTag;
    }

    public function getCopiedTag(): Tag
    {
        $this->copiedTag ??
            throw new UnexpectedValueException(
                sprintf(
                    'Return value is not set or not a type of %s. Check with hasCopiedTag() or set it with setCopiedTag() before you call the getter.',
                    Tag::class,
                ),
            );

        return $this->copiedTag;
    }

    public function setCopiedTag(Tag $copiedTag): void
    {
        $this->copiedTag = $copiedTag;
    }

    public function hasCopiedTag(): bool
    {
        return isset($this->copiedTag);
    }
}
