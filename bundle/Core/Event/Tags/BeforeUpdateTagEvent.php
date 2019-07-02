<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Event\Tags;

use eZ\Publish\Core\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;
use UnexpectedValueException;

final class BeforeUpdateTagEvent extends BeforeEvent
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct
     */
    private $tagUpdateStruct;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag|null
     */
    private $updatedTag;

    public function __construct(TagUpdateStruct $tagUpdateStruct, Tag $tag)
    {
        $this->tagUpdateStruct = $tagUpdateStruct;
        $this->tag = $tag;
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
        if ($this->updatedTag === null) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check with hasUpdatedTag() or set it with setUpdatedTag() before you call the getter.', Tag::class));
        }

        return $this->updatedTag;
    }

    public function setUpdatedTag(?Tag $updatedTag): void
    {
        $this->updatedTag = $updatedTag;
    }

    public function hasUpdatedTag(): bool
    {
        return $this->updatedTag instanceof Tag;
    }
}
