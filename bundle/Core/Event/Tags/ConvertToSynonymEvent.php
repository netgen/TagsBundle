<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Event\Tags;

use eZ\Publish\Core\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class ConvertToSynonymEvent extends AfterEvent
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $synonym;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $mainTag;

    public function __construct(Tag $synonym, Tag $mainTag)
    {
        $this->synonym = $synonym;
        $this->mainTag = $mainTag;
    }

    public function getSynonym(): Tag
    {
        return $this->synonym;
    }

    public function getMainTag(): Tag
    {
        return $this->mainTag;
    }
}
