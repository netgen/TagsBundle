<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

interface BeforeMoveSubtreeEvent extends BeforeEvent
{
    public function getTag(): Tag;

    public function getParentTag(): ?Tag;

    public function getMovedTag(): Tag;

    public function setMovedTag(?Tag $movedTag): void;

    public function hasMovedTag(): bool;
}
