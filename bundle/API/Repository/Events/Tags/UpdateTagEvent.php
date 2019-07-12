<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use eZ\Publish\SPI\Repository\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;

interface UpdateTagEvent extends AfterEvent
{
    public function getTagUpdateStruct(): TagUpdateStruct;

    public function getTag(): Tag;
}
