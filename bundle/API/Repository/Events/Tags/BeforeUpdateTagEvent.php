<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use Ibexa\Contracts\Core\Repository\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;
use UnexpectedValueException;

use function sprintf;

final class BeforeUpdateTagEvent extends BeforeEvent
{
    private Tag $updatedTag;

    public function __construct(private TagUpdateStruct $tagUpdateStruct, private Tag $tag)
    {
    }

    public function getTagUpdateStruct(): TagUpdateStruct
    {
        return $this->tagUpdateStruct;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function getUpdatedTag(): Tag
    {
        $this->updatedTag ??
            throw new UnexpectedValueException(
                sprintf(
                    'Return value is not set or not a type of %s. Check with hasUpdatedTag() or set it with setUpdatedTag() before you call the getter.',
                    Tag::class,
                ),
            );

        return $this->updatedTag;
    }

    public function setUpdatedTag(Tag $updatedTag): void
    {
        $this->updatedTag = $updatedTag;
    }

    public function hasUpdatedTag(): bool
    {
        return isset($this->updatedTag);
    }
}
