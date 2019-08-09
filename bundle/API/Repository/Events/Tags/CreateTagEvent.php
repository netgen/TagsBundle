<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use eZ\Publish\SPI\Repository\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;

final class CreateTagEvent extends AfterEvent
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct
     */
    private $tagCreateStruct;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

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
