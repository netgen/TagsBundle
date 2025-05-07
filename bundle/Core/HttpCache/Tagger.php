<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\HttpCache;

use Ibexa\HttpCache\Handler\TagHandler;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

use function array_map;

final readonly class Tagger
{
    public const TAG_PREFIX = 'ngt';
    public const MAIN_TAG_PREFIX = 'ngtm';
    public const PATH_TAG_PREFIX = 'ngtp';

    public function __construct(
        private TagHandler $handler,
    ) {}

    public function resolveTags(Tag $tag): array
    {
        $tags = [
            self::TAG_PREFIX . $tag->id,
        ];

        if ($tag->isSynonym()) {
            $tags[] = self::MAIN_TAG_PREFIX . $tag->mainTagId;
        }

        foreach ($tag->path as $tagId) {
            if ($tagId !== $tag->id) {
                $tags[] = self::PATH_TAG_PREFIX . $tagId;
            }
        }

        return $tags;
    }

    public function tagByTag(Tag $tag): void
    {
        $this->handler->addTags($this->resolveTags($tag));
    }

    public function tagByTagId(int $tagId): void
    {
        $this->handler->addTags([self::TAG_PREFIX . $tagId]);
    }

    /**
     * @param int[] $tagIds
     */
    public function tagByTagIds(array $tagIds): void
    {
        $this->handler->addTags(
            array_map(
                static fn (int $tagId) => self::TAG_PREFIX . $tagId,
                $tagIds,
            ),
        );
    }
}
