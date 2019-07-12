<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Event\Tags;

use Netgen\TagsBundle\API\Repository\Events\Tags\MergeTagsEvent as MergeTagsEventInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Contracts\EventDispatcher\Event;

final class MergeTagsEvent extends Event implements MergeTagsEventInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $targetTag;

    public function __construct(Tag $targetTag)
    {
        $this->targetTag = $targetTag;
    }

    public function getTargetTag(): Tag
    {
        return $this->targetTag;
    }
}
