<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Event\Tags;

use eZ\Publish\Core\Event\AfterEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class DeleteTagEvent extends AfterEvent
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    public function __construct(Tag $tag)
    {
        $this->tag = $tag;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }
}
