<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use UnexpectedValueException;
use function sprintf;

final class BeforeCopySubtreeEvent extends BeforeEvent
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
    private $copiedTag;

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

    public function getCopiedTag(): Tag
    {
        if ($this->copiedTag === null) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check with hasCopiedTag() or set it with setCopiedTag() before you call the getter.', Tag::class));
        }

        return $this->copiedTag;
    }

    public function setCopiedTag(?Tag $copiedTag): void
    {
        $this->copiedTag = $copiedTag;
    }

    public function hasCopiedTag(): bool
    {
        return $this->copiedTag instanceof Tag;
    }
}
