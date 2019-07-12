<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;

interface BeforeUpdateTagEvent extends BeforeEvent
{
    public function getTagUpdateStruct(): TagUpdateStruct;

    public function getTag(): Tag;

    public function getUpdatedTag(): Tag;

    public function setUpdatedTag(?Tag $updatedTag): void;

    public function hasUpdatedTag(): bool;
}
