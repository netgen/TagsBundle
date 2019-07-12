<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use eZ\Publish\SPI\Repository\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

interface MoveSubtreeEvent extends AfterEvent
{
    public function getTag(): Tag;

    public function getParentTag(): ?Tag;
}
