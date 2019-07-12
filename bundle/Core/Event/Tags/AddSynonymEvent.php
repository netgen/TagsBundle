<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Event\Tags;

use Netgen\TagsBundle\API\Repository\Events\Tags\AddSynonymEvent as AddSynonymEventInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Contracts\EventDispatcher\Event;

final class AddSynonymEvent extends Event implements AddSynonymEventInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct
     */
    private $synonymCreateStruct;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $synonym;

    public function __construct(SynonymCreateStruct $synonymCreateStruct, Tag $synonym)
    {
        $this->synonymCreateStruct = $synonymCreateStruct;
        $this->synonym = $synonym;
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
