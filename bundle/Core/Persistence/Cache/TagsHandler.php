<?php

namespace Netgen\TagsBundle\Core\Persistence\Cache;

use eZ\Publish\Core\Persistence\Cache\AbstractInMemoryHandler;
use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\Handler as TagsHandlerInterface;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class TagsHandler extends AbstractInMemoryHandler implements TagsHandlerInterface
{
    const ALL_TRANSLATIONS_KEY = '0';

    /**
     * @var \Netgen\TagsBundle\SPI\Persistence\Tags\Handler
     */
    protected $tagsHandler;

    public function __construct(
        TagAwareAdapterInterface $cache,
        PersistenceLogger $logger,
        $inMemory,
        TagsHandlerInterface $tagsHandler
    ) {
        // No type hint on internal classes, parent::__construct will take care of checking that it gets what it expects.
        parent::__construct($cache, $logger, $inMemory);
        $this->tagsHandler = $tagsHandler;
    }

    public function load($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        $translationsKey = empty($translations) ? self::ALL_TRANSLATIONS_KEY : implode('|', $translations);
        $keySuffix = '-' . $translationsKey . '-' . ($useAlwaysAvailable ? '1' : '0');

        return $this->getCacheValue(
            $tagId,
            'netgen-tag-',
            function (int $tagId) use ($translations, $useAlwaysAvailable): Tag {
                return $this->tagsHandler->load($tagId, $translations, $useAlwaysAvailable);
            },
            static function (Tag $tag): array {
                $tags[] = 'tag-' . $tag->id;
                foreach (\explode('/', trim($tag->pathString, '/')) as $pathId) {
                    $tags[] = 'tag-path-' . $pathId;
                }

                return $tags;
            },
            static function (Tag $tag) use ($keySuffix): array {
                return ['netgen-tag-' . $tag->id . $keySuffix];
            },
            $keySuffix
        );
    }

    public function loadList(array $tagIds, array $translations = null, $useAlwaysAvailable = true)
    {
        $translationsKey = empty($translations) ? self::ALL_TRANSLATIONS_KEY : implode('|', $translations);
        $keySuffix = '-' . $translationsKey . '-' . ($useAlwaysAvailable ? '1' : '0');

        return $this->getMultipleCacheValues(
            $tagIds,
            'netgen-tag-',
            function (array $tagIds) use ($translations, $useAlwaysAvailable): array {
                return $this->tagsHandler->loadList($tagIds, $translations, $useAlwaysAvailable);
            },
            static function (Tag $tag): array {
                $tags[] = 'tag-' . $tag->id;
                foreach (\explode('/', trim($tag->pathString, '/')) as $pathId) {
                    $tags[] = 'tag-path-' . $pathId;
                }

                return $tags;
            },
            static function (Tag $tag) use ($keySuffix): array {
                return ['netgen-tag-' . $tag->id . $keySuffix];
            },
            $keySuffix
        );
    }

    public function loadTagInfo($tagId)
    {
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

    public function loadByRemoteId($remoteId, array $translations = null, $useAlwaysAvailable = true)
    {
        $translationsKey = empty($translations) ? self::ALL_TRANSLATIONS_KEY : implode('|', $translations);
        $alwaysAvailableKey = $useAlwaysAvailable ? '1' : '0';
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

    public function loadTagInfoByRemoteId($remoteId)
    {
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

    public function loadTagByKeywordAndParentId($keyword, $parentTagId, array $translations = null, $useAlwaysAvailable = true)
    {
        $this->logger->logCall(__METHOD__, ['keyword' => $keyword, 'parentTag' => $parentTagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        return $this->tagsHandler->loadTagByKeywordAndParentId($keyword, $parentTagId, $translations, $useAlwaysAvailable);
    }

    public function loadChildren($tagId, $offset = 0, $limit = -1, array $translations = null, $useAlwaysAvailable = true)
    {
        $this->logger->logCall(__METHOD__, ['tag' => $tagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        return $this->tagsHandler->loadChildren($tagId, $offset, $limit, $translations, $useAlwaysAvailable);
    }

    public function getChildrenCount($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        $this->logger->logCall(__METHOD__, ['tag' => $tagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        return $this->tagsHandler->getChildrenCount($tagId, $translations, $useAlwaysAvailable);
    }

    public function loadTagsByKeyword($keyword, $translation, $useAlwaysAvailable = true, $offset = 0, $limit = -1)
    {
        $this->logger->logCall(__METHOD__, ['keyword' => $keyword, 'translation' => $translation, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        return $this->tagsHandler->loadTagsByKeyword($keyword, $translation, $useAlwaysAvailable, $offset, $limit);
    }

    public function getTagsByKeywordCount($keyword, $translation, $useAlwaysAvailable = true)
    {
        $this->logger->logCall(__METHOD__, ['keyword' => $keyword, 'translation' => $translation, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        return $this->tagsHandler->getTagsByKeywordCount($keyword, $translation, $useAlwaysAvailable);
    }

    public function searchTags($searchString, $translation, $useAlwaysAvailable = true, $offset = 0, $limit = -1)
    {
        $this->logger->logCall(__METHOD__, ['searchString' => $searchString, 'translation' => $translation, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        return $this->tagsHandler->searchTags($searchString, $translation, $useAlwaysAvailable, $offset, $limit);
    }

    public function loadSynonyms($tagId, $offset = 0, $limit = -1, array $translations = null, $useAlwaysAvailable = true)
    {
        // Method caches all synonyms in cache and only uses offset / limit to slice the cached result
        $translationsKey = empty($translations) ? self::ALL_TRANSLATIONS_KEY : implode('|', $translations);
        $alwaysAvailableKey = $useAlwaysAvailable ? '1' : '0';
        $cacheItem = $this->cache->getItem("netgen-tag-synonyms-{$tagId}-{$translationsKey}-{$alwaysAvailableKey}");
        if ($cacheItem->isHit()) {
            return array_slice($cacheItem->get(), $offset, $limit > -1 ? $limit : null);
        }

        $this->logger->logCall(__METHOD__, ['tag' => $tagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        $tagInfo = $this->loadTagInfo($tagId);
        $synonyms = $this->tagsHandler->loadSynonyms($tagId, 0, null, $translations, $useAlwaysAvailable);

        $cacheItem->set($synonyms);
        $cacheTags = $this->getCacheTags($tagInfo->id, $tagInfo->pathString);
        foreach ($synonyms as $synonym) {
            $cacheTags = array_merge($cacheTags, $this->getCacheTags($synonym->id, $synonym->pathString));
        }
        $cacheItem->tag(array_unique($cacheTags));
        $this->cache->save($cacheItem);

        return array_slice($synonyms, $offset, $limit > -1 ? $limit : null);
    }

    public function getSynonymCount($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        $this->logger->logCall(__METHOD__, ['tag' => $tagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable]);

        return $this->tagsHandler->getSynonymCount($tagId, $translations, $useAlwaysAvailable);
    }

    public function create(CreateStruct $createStruct)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $createStruct]);

        return $this->tagsHandler->create($createStruct);
    }

    public function update(UpdateStruct $updateStruct, $tagId)
    {
        $this->logger->logCall(__METHOD__, ['tag' => $tagId, 'struct' => $updateStruct]);
        $updatedTag = $this->tagsHandler->update($updateStruct, $tagId);

        $this->cache->invalidateTags(['tag-' . $tagId]);

        return $updatedTag;
    }

    public function addSynonym(SynonymCreateStruct $createStruct)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $createStruct]);
        $synonym = $this->tagsHandler->addSynonym($createStruct);

        $this->cache->invalidateTags(['tag-' . $createStruct->mainTagId]);

        return $synonym;
    }

    public function convertToSynonym($tagId, $mainTagId)
    {
        $this->logger->logCall(__METHOD__, ['tag' => $tagId]);
        $synonym = $this->tagsHandler->convertToSynonym($tagId, $mainTagId);

        $this->cache->invalidateTags(['tag-' . $tagId, 'tag-' . $mainTagId]);

        return $synonym;
    }

    public function merge($tagId, $targetTagId)
    {
        $this->logger->logCall(__METHOD__, ['tag' => $tagId, 'targetTag' => $targetTagId]);

        $this->tagsHandler->merge($tagId, $targetTagId);

        $this->cache->invalidateTags(['tag-path-' . $tagId, 'tag-path-' . $targetTagId]);
    }

    public function copySubtree($sourceId, $destinationParentId)
    {
        $this->logger->logCall(__METHOD__, ['sourceTag' => $sourceId, 'destinationTag' => $destinationParentId]);

        $return = $this->tagsHandler->copySubtree($sourceId, $destinationParentId);

        $this->cache->invalidateTags(['tag-path-' . $sourceId, 'tag-path-' . $destinationParentId]);

        return $return;
    }

    public function moveSubtree($sourceId, $destinationParentId)
    {
        $this->logger->logCall(__METHOD__, ['sourceTag' => $sourceId, 'destinationTag' => $destinationParentId]);

        $return = $this->tagsHandler->moveSubtree($sourceId, $destinationParentId);

        $this->cache->invalidateTags(['tag-path-' . $sourceId, 'tag-path-' . $destinationParentId]);

        return $return;
    }

    public function deleteTag($tagId)
    {
        $this->logger->logCall(__METHOD__, ['tag' => $tagId]);
        $this->tagsHandler->deleteTag($tagId);

        $this->cache->invalidateTags(['tag-path-' . $tagId]);
    }

    /**
     * Return relevant cache tags so cache can be purged reliably.
     *
     * @param int $tagId
     * @param string $pathString
     * @param array $tags optional, can be used to specify additional tags
     *
     * @return array
     */
    private function getCacheTags($tagId, $pathString, array $tags = [])
    {
        $tags[] = 'tag-' . $tagId;

        foreach (explode('/', trim($pathString, '/')) as $pathId) {
            $tags[] = 'tag-path-' . $pathId;
        }

        return $tags;
    }
}
