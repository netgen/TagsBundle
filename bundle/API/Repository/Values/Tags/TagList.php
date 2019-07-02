<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Tags;

use Doctrine\Common\Collections\ArrayCollection;

final class TagList extends ArrayCollection
{
    public function __construct(array $tags = [])
    {
        parent::__construct(
            array_filter(
                $tags,
                static function (Tag $tag): bool {
                    return true;
                }
            )
        );
    }

    /**
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public function getTags(): array
    {
        return $this->toArray();
    }

    /**
     * @return int[]
     */
    public function getTagIds(): array
    {
        return array_map(
            static function (Tag $tag) {
                return $tag->id;
            },
            $this->getTags()
        );
    }
}
