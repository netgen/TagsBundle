<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use eZ\Publish\SPI\Repository\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

interface CopySubtreeEvent extends AfterEvent
{
    public function getTag(): Tag;

    public function getCopiedTag(): Tag;

    public function getParentTag(): ?Tag;
}
