<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use eZ\Publish\SPI\Repository\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

interface AddSynonymEvent extends AfterEvent
{
    public function getSynonymCreateStruct(): SynonymCreateStruct;

    public function getSynonym(): Tag;
}
