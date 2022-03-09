<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use Ibexa\Contracts\Core\Repository\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;

final class CreateTagEvent extends AfterEvent
{
    private TagCreateStruct $tagCreateStruct;

    private Tag $tag;

    public function __construct(TagCreateStruct $tagCreateStruct, Tag $tag)
    {
        $this->tagCreateStruct = $tagCreateStruct;
        $this->tag = $tag;
    }

    public function getTagCreateStruct(): TagCreateStruct
    {
        return $this->tagCreateStruct;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }
}
