<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Event\Tags;

use Netgen\TagsBundle\API\Repository\Events\Tags\UpdateTagEvent as UpdateTagEventInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;
use Symfony\Contracts\EventDispatcher\Event;

final class UpdateTagEvent extends Event implements UpdateTagEventInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct
     */
    private $tagUpdateStruct;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

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
