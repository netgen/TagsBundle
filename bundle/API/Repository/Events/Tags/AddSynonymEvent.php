<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use Ibexa\Contracts\Core\Repository\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class AddSynonymEvent extends AfterEvent
{
    public function __construct(private SynonymCreateStruct $synonymCreateStruct, private Tag $synonym)
    {
    }

    public function getSynonymCreateStruct(): SynonymCreateStruct
    {
        return $this->synonymCreateStruct;
    }

    public function getSynonym(): Tag
    {
        return $this->synonym;
    }
}
