<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Persistence\Cache;

use Ibexa\Core\Persistence\Cache\AbstractInMemoryHandler;
use Ibexa\Core\Persistence\Cache\InMemory\InMemoryCache;
use Ibexa\Core\Persistence\Cache\PersistenceLogger;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\Handler as TagsHandlerInterface;
use Netgen\TagsBundle\SPI\Persistence\Tags\SearchResult;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag;
use Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

use function array_merge;
use function array_slice;
use function array_unique;
use function count;
use function explode;
use function implode;
use function trim;

final class TagsHandler extends AbstractInMemoryHandler implements TagsHandlerInterface
{
    private const ALL_TRANSLATIONS_KEY = '0';

    public function __construct(
        TagAwareAdapterInterface $cache,
        PersistenceLogger $logger,
        InMemoryCache $inMemory,
        private TagsHandlerInterface $tagsHandler,
    ) {
        // No type hint on internal classes, parent::__construct will take care of checking that it gets what it expects.
        parent::__construct($cache, $logger, $inMemory);
    }

    public function load(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): Tag
    {
        $translationsKey = count($translations ?? []) === 0 ?
            self::ALL_TRANSLATIONS_KEY :
            implode('|', $translations);

        $keySuffix = '-' . $translationsKey . '-' . ($useAlwaysAvailable ? '1' : '0');

        /** @var \Netgen\TagsBundle\SPI\Persistence\Tags\Tag $cacheValue */
        $cacheValue = $this->getCacheValue(
            $tagId,
            'netgen-tag-',
            fn (int $tagId): Tag => $this->tagsHandler->load($tagId, $translations, $useAlwaysAvailable),
            static function (Tag $tag): array {
                $tags = ['tag-' . $tag->id];
                foreach (explode('/', trim($tag->pathString, '/')) as $pathId) {
                    $tags[] = 'tag-path-' . $pathId;
                }

                return $tags;
            },
            static fn (Tag $tag): array => ['netgen-tag-' . $tag->id . $keySuffix],
            $keySuffix,
        );

        return $cacheValue;
    }

    public function loadList(array $tagIds, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        $translationsKey = count($translations ?? []) === 0 ?
            self::ALL_TRANSLATIONS_KEY :
            implode('|', $translations);

        $keySuffix = '-' . $translationsKey . '-' . ($useAlwaysAvailable ? '1' : '0');

        return $this->getMultipleCacheValues(
            $tagIds,
            'netgen-tag-',
            fn (array $tagIds): array => $this->tagsHandler->loadList($tagIds, $translations, $useAlwaysAvailable),
            static function (Tag $tag): array {
                $tags = ['tag-' . $tag->id];
                foreach (explode('/', trim($tag->pathString, '/')) as $pathId) {
                    $tags[] = 'tag-path-' . $pathId;
                }

                return $tags;
            },
            static fn (Tag $tag): array => ['netgen-tag-' . $tag->id . $keySuffix],
            $keySuffix,
        );
    }

    public function loadTagInfo(int $tagId): TagInfo
    {
        /** @var \Symfony\Component\Cache\CacheItem $cacheItem */
        $cacheItem = $this->cache->getItem("netgen-tag-info-{$tagId}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, ['tag' => $tagId]);
        $tagInfo = $this->tagsHandler->loadTagInfo($tagId);
        $cacheItem->set($tagInfo);
        $cacheItem->tag($this->getCacheTags($tagInfo->id, $tagInfo->pathString));
        $this->cache->save($cacheItem);

        return $tagInfo;
    }

    public function loadByRemoteId(string $remoteId, ?array $translations = null, bool $useAlwaysAvailable = true): Tag
    {
        $translationsKey = count($translations ?? []) === 0 ?
            self::ALL_TRANSLATIONS_KEY :
            implode('|', $translations);

        $alwaysAvailableKey = $useAlwaysAvailable ? '1' : '0';

        /** @var \Symfony\Component\Cache\CacheItem $cacheItem */
        $cacheItem = $this->cache->getItem("netgen-tag-byRemoteId-{$remoteId}-{$translationsKey}-{$alwaysAvailableKey}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, ['tag' => $remoteId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable]);
        $tag = $this->tagsHandler->loadByRemoteId($remoteId, $translations, $useAlwaysAvailable);
        $cacheItem->set($tag);
        $cacheItem->tag($this->getCacheTags($tag->id, $tag->pathString));
        $this->cache->save($cacheItem);

        return $tag;
    }

    public function loadTagInfoByRemoteId(string $remoteId): TagInfo
    {
        /** @var \Symfony\Component\Cache\CacheItem $cacheItem */
        $cacheItem = $this->cache->getItem("netgen-tag-info-byRemoteId-{$remoteId}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, ['tag' => $remoteId]);
        $tagInfo = $this->tagsHandler->loadTagInfoByRemoteId($remoteId);
        $cacheItem->set($tagInfo);
        $cacheItem->tag($this->getCacheTags($tagInfo->id, $tagInfo->pathString));
        $this->cache->save($cacheItem);

        return $tagInfo;
    }

    public function loadTagByKeywordAndParentId(string $keyword, int $parentTagId, ?array $translations = null, bool $useAlwaysAvailable = true): Tag
    {
        $this->logger->logCall(__METHOD__, ['keyword' => $keyword, 'parentTag' => $parentTagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        return $this->tagsHandler->loadTagByKeywordAndParentId($keyword, $parentTagId, $translations, $useAlwaysAvailable);
    }

    public function loadChildren(int $tagId, int $offset = 0, int $limit = -1, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        $this->logger->logCall(__METHOD__, ['tag' => $tagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        return $this->tagsHandler->loadChildren($tagId, $offset, $limit, $translations, $useAlwaysAvailable);
    }

    public function getChildrenCount(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): int
    {
        $this->logger->logCall(__METHOD__, ['tag' => $tagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        return $this->tagsHandler->getChildrenCount($tagId, $translations, $useAlwaysAvailable);
    }

    public function loadTagsByKeyword(string $keyword, string $translation, bool $useAlwaysAvailable = true, int $offset = 0, int $limit = -1): array
    {
        $this->logger->logCall(__METHOD__, ['keyword' => $keyword, 'translation' => $translation, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        return $this->tagsHandler->loadTagsByKeyword($keyword, $translation, $useAlwaysAvailable, $offset, $limit);
    }

    public function getTagsByKeywordCount(string $keyword, string $translation, bool $useAlwaysAvailable = true): int
    {
        $this->logger->logCall(__METHOD__, ['keyword' => $keyword, 'translation' => $translation, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        return $this->tagsHandler->getTagsByKeywordCount($keyword, $translation, $useAlwaysAvailable);
    }

    public function searchTags(string $searchString, string $translation, bool $useAlwaysAvailable = true, int $offset = 0, int $limit = -1): SearchResult
    {
        $this->logger->logCall(__METHOD__, ['searchString' => $searchString, 'translation' => $translation, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        return $this->tagsHandler->searchTags($searchString, $translation, $useAlwaysAvailable, $offset, $limit);
    }

    public function loadSynonyms(int $tagId, int $offset = 0, int $limit = -1, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        // Method caches all synonyms in cache and only uses offset / limit to slice the cached result
        $translationsKey = count($translations ?? []) === 0 ?
            self::ALL_TRANSLATIONS_KEY :
            implode('|', $translations);

        $alwaysAvailableKey = $useAlwaysAvailable ? '1' : '0';

        /** @var \Symfony\Component\Cache\CacheItem $cacheItem */
        $cacheItem = $this->cache->getItem("netgen-tag-synonyms-{$tagId}-{$translationsKey}-{$alwaysAvailableKey}");
        if ($cacheItem->isHit()) {
            return array_slice($cacheItem->get(), $offset, $limit > -1 ? $limit : null);
        }

        $this->logger->logCall(__METHOD__, ['tag' => $tagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        $tagInfo = $this->loadTagInfo($tagId);
        $synonyms = $this->tagsHandler->loadSynonyms($tagId, 0, -1, $translations, $useAlwaysAvailable);

        $cacheItem->set($synonyms);
        $cacheTags = [$this->getCacheTags($tagInfo->id, $tagInfo->pathString)];
        foreach ($synonyms as $synonym) {
            $cacheTags[] = $this->getCacheTags($synonym->id, $synonym->pathString);
        }
        $cacheItem->tag(array_unique(array_merge(...$cacheTags)));
        $this->cache->save($cacheItem);

        return array_slice($synonyms, $offset, $limit > -1 ? $limit : null);
    }

    public function getSynonymCount(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): int
    {
        $this->logger->logCall(__METHOD__, ['tag' => $tagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        return $this->tagsHandler->getSynonymCount($tagId, $translations, $useAlwaysAvailable);
    }

    public function create(CreateStruct $createStruct): Tag
    {
        $this->logger->logCall(__METHOD__, ['struct' => $createStruct]);

        return $this->tagsHandler->create($createStruct);
    }

    public function update(UpdateStruct $updateStruct, int $tagId): Tag
    {
        $this->logger->logCall(__METHOD__, ['tag' => $tagId, 'struct' => $updateStruct]);
        $updatedTag = $this->tagsHandler->update($updateStruct, $tagId);

        $this->cache->invalidateTags(['tag-' . $tagId]);

        return $updatedTag;
    }

    public function addSynonym(SynonymCreateStruct $createStruct): Tag
    {
        $this->logger->logCall(__METHOD__, ['struct' => $createStruct]);
        $synonym = $this->tagsHandler->addSynonym($createStruct);

        $this->cache->invalidateTags(['tag-' . $createStruct->mainTagId]);

        return $synonym;
    }

    public function convertToSynonym(int $tagId, int $mainTagId): Tag
    {
        $this->logger->logCall(__METHOD__, ['tag' => $tagId]);
        $synonym = $this->tagsHandler->convertToSynonym($tagId, $mainTagId);

        $this->cache->invalidateTags(['tag-' . $tagId, 'tag-' . $mainTagId]);

        return $synonym;
    }

    public function merge(int $tagId, int $targetTagId): void
    {
        $this->logger->logCall(__METHOD__, ['tag' => $tagId, 'targetTag' => $targetTagId]);

        $this->tagsHandler->merge($tagId, $targetTagId);

        $this->cache->invalidateTags(['tag-path-' . $tagId, 'tag-path-' . $targetTagId]);
    }

    public function copySubtree(int $sourceId, int $destinationParentId): Tag
    {
        $this->logger->logCall(__METHOD__, ['sourceTag' => $sourceId, 'destinationTag' => $destinationParentId]);

        $return = $this->tagsHandler->copySubtree($sourceId, $destinationParentId);

        $this->cache->invalidateTags(['tag-path-' . $sourceId, 'tag-path-' . $destinationParentId]);

        return $return;
    }

    public function moveSubtree(int $sourceId, int $destinationParentId): Tag
    {
        $this->logger->logCall(__METHOD__, ['sourceTag' => $sourceId, 'destinationTag' => $destinationParentId]);

        $return = $this->tagsHandler->moveSubtree($sourceId, $destinationParentId);

        $this->cache->invalidateTags(['tag-path-' . $sourceId, 'tag-path-' . $destinationParentId]);

        return $return;
    }

    public function deleteTag(int $tagId): void
    {
        $this->logger->logCall(__METHOD__, ['tag' => $tagId]);
        $this->tagsHandler->deleteTag($tagId);

        $this->cache->invalidateTags(['tag-path-' . $tagId]);
    }

    /**
     * Return relevant cache tags so cache can be purged reliably.
     *
     * $tags argument is optional. Can be used to specify additional tags.
     */
    private function getCacheTags(int $tagId, string $pathString, array $tags = []): array
    {
        $tags[] = 'tag-' . $tagId;

        foreach (explode('/', trim($pathString, '/')) as $pathId) {
            $tags[] = 'tag-path-' . $pathId;
        }

        return $tags;
    }
}
