<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use Ibexa\Contracts\Core\Repository\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class BeforeRevealTagEvent extends BeforeEvent
{
    public function __construct(private readonly Tag $tag) {}

    public function getTag(): Tag
    {
        return $this->tag;
    }
}
