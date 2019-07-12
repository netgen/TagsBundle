<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

interface BeforeConvertToSynonymEvent extends BeforeEvent
{
    public function getTag(): Tag;

    public function getMainTag(): Tag;

    public function getSynonym(): Tag;

    public function setSynonym(?Tag $synonym): void;

    public function hasSynonym(): bool;
}
