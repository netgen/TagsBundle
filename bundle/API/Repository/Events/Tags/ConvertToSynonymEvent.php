<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use Ibexa\Contracts\Core\Repository\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class ConvertToSynonymEvent extends AfterEvent
{
    public function __construct(private Tag $synonym, private Tag $mainTag) {}

    public function getSynonym(): Tag
    {
        return $this->synonym;
    }

    public function getMainTag(): Tag
    {
        return $this->mainTag;
    }
}
