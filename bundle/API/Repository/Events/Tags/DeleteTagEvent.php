<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use Ibexa\Contracts\Core\Repository\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class DeleteTagEvent extends AfterEvent
{
    public function __construct(private Tag $tag) {}

    public function getTag(): Tag
    {
        return $this->tag;
    }
}
