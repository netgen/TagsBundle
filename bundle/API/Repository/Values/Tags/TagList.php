<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Tags;

use Doctrine\Common\Collections\ArrayCollection;
use function array_filter;
use function array_map;

/**
 * @extends \Doctrine\Common\Collections\ArrayCollection<array-key, \Netgen\TagsBundle\API\Repository\Values\Tags\Tag>
 */
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
     * @return array<array-key, \Netgen\TagsBundle\API\Repository\Values\Tags\Tag>
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
            static function (Tag $tag): int {
                return (int) $tag->id;
            },
            $this->getTags()
        );
    }
}
