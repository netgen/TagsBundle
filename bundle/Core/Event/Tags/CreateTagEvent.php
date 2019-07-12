<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Event\Tags;

use Netgen\TagsBundle\API\Repository\Events\Tags\CreateTagEvent as CreateTagEventInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Symfony\Contracts\EventDispatcher\Event;

final class CreateTagEvent extends Event implements CreateTagEventInterface
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
