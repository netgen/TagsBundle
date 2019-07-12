<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

interface BeforeDeleteTagEvent extends BeforeEvent
{
    public function getTag(): Tag;
}
