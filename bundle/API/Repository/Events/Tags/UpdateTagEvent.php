<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use Ibexa\Contracts\Core\Repository\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;

final class UpdateTagEvent extends AfterEvent
{
    private TagUpdateStruct $tagUpdateStruct;

    private Tag $tag;

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
}
