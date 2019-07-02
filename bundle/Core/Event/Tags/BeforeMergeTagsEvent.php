<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Event\Tags;

use eZ\Publish\Core\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class BeforeMergeTagsEvent extends BeforeEvent
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $targetTag;

    public function __construct(Tag $tag, Tag $targetTag)
    {
        $this->tag = $tag;
        $this->targetTag = $targetTag;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function getTargetTag(): Tag
    {
        return $this->targetTag;
    }
}
