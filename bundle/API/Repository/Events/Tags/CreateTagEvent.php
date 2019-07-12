<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use eZ\Publish\SPI\Repository\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;

interface CreateTagEvent extends AfterEvent
{
    public function getTagCreateStruct(): TagCreateStruct;

    public function getTag(): Tag;
}
