<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;

interface BeforeCreateTagEvent extends BeforeEvent
{
    public function getTagCreateStruct(): TagCreateStruct;

    public function getTag(): Tag;

    public function setTag(?Tag $tag): void;

    public function hasTag(): bool;
}
