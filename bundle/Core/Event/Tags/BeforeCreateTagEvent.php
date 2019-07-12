<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Event\Tags;

use Netgen\TagsBundle\API\Repository\Events\Tags\BeforeCreateTagEvent as BeforeCreateTagEventInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Symfony\Contracts\EventDispatcher\Event;
use UnexpectedValueException;

final class BeforeCreateTagEvent extends Event implements BeforeCreateTagEventInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct
     */
    private $tagCreateStruct;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag|null
     */
    private $tag;

    public function __construct(TagCreateStruct $tagCreateStruct)
    {
        $this->tagCreateStruct = $tagCreateStruct;
    }

    public function getTagCreateStruct(): TagCreateStruct
    {
        return $this->tagCreateStruct;
    }

    public function getTag(): Tag
    {
        if ($this->tag === null) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check with hasTag() or set it with setTag() before you call the getter.', Tag::class));
        }

        return $this->tag;
    }

    public function setTag(?Tag $tag): void
    {
        $this->tag = $tag;
    }

    public function hasTag(): bool
    {
        return $this->tag instanceof Tag;
    }
}
