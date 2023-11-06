<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use Ibexa\Contracts\Core\Repository\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;

final class UpdateTagEvent extends AfterEvent
{
    public function __construct(private TagUpdateStruct $tagUpdateStruct, private Tag $tag) {}

    public function getTagUpdateStruct(): TagUpdateStruct
    {
        return $this->tagUpdateStruct;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }
}
