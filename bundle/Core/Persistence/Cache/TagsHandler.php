<?php

namespace Netgen\TagsBundle\Core\Persistence\Cache;

use eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator;
use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\Handler as TagsHandlerInterface;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;

class TagsHandler implements TagsHandlerInterface
{
    const ALL_TRANSLATIONS_KEY = '0';

    /**
     * @var \eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator
     */
    protected $cache;

    /**
     * @var \Netgen\TagsBundle\SPI\Persistence\Tags\Handler
     */
    protected $tagsHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Cache\PersistenceLogger
     */
    protected $logger;

    public function __construct(CacheServiceDecorator $cache, TagsHandlerInterface $tagsHandler, PersistenceLogger $logger)
    {
        $this->cache = $cache;
        $this->tagsHandler = $tagsHandler;
        $this->logger = $logger;
    }

    public function load($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        $translationsKey = empty($translations) ? self::ALL_TRANSLATIONS_KEY : implode('|', $translations);
        $alwaysAvailableKey = $useAlwaysAvailable ? '1' : '0';
        $cache = $this->cache->getItem('tag', $tagId, $translationsKey, $alwaysAvailableKey);
        $tag = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('tag' => $tagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable));
            $cache->set($tag = $this->tagsHandler->load($tagId, $translations, $useAlwaysAvailable))->save();
        }

        return $tag;
    }

    public function loadTagInfo($tagId)
    {
        $cache = $this->cache->getItem('tag', 'info', $tagId);
        $tagInfo = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('tag' => $tagId));
            $cache->set($tagInfo = $this->tagsHandler->loadTagInfo($tagId))->save();
        }

        return $tagInfo;
    }

    public function loadByRemoteId($remoteId, array $translations = null, $useAlwaysAvailable = true)
    {
        $translationsKey = empty($translations) ? self::ALL_TRANSLATIONS_KEY : implode('|', $translations);
        $alwaysAvailableKey = $useAlwaysAvailable ? '1' : '0';
        $cache = $this->cache->getItem('tag', 'remoteId', $remoteId, $translationsKey, $alwaysAvailableKey);
        $tag = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('tag' => $remoteId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable));
            $cache->set($tag = $this->tagsHandler->loadByRemoteId($remoteId, $translations, $useAlwaysAvailable))->save();
        }

        return $tag;
    }

    public function loadTagInfoByRemoteId($remoteId)
    {
        $cache = $this->cache->getItem('tag', 'info', 'remoteId', $remoteId);
        $tagInfo = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('tag' => $remoteId));
            $cache->set($tagInfo = $this->tagsHandler->loadTagInfoByRemoteId($remoteId))->save();
        }

        return $tagInfo;
    }

    public function loadTagByKeywordAndParentId($keyword, $parentTagId, array $translations = null, $useAlwaysAvailable = true)
    {
        $this->logger->logCall(__METHOD__, array('keyword' => $keyword, 'parentTag' => $parentTagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable));

        return $this->tagsHandler->loadTagByKeywordAndParentId($keyword, $parentTagId, $translations, $useAlwaysAvailable);
    }

    public function loadChildren($tagId, $offset = 0, $limit = -1, array $translations = null, $useAlwaysAvailable = true)
    {
        $this->logger->logCall(__METHOD__, array('tag' => $tagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable));

        return $this->tagsHandler->loadChildren($tagId, $offset, $limit, $translations, $useAlwaysAvailable);
    }

    public function getChildrenCount($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        $this->logger->logCall(__METHOD__, array('tag' => $tagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable));

        return $this->tagsHandler->getChildrenCount($tagId, $translations, $useAlwaysAvailable);
    }

    public function loadTagsByKeyword($keyword, $translation, $useAlwaysAvailable = true, $offset = 0, $limit = -1)
    {
        $this->logger->logCall(__METHOD__, array('keyword' => $keyword, 'translation' => $translation, 'useAlwaysAvailable' => $useAlwaysAvailable));

        return $this->tagsHandler->loadTagsByKeyword($keyword, $translation, $useAlwaysAvailable, $offset, $limit);
    }

    public function getTagsByKeywordCount($keyword, $translation, $useAlwaysAvailable = true)
    {
        $this->logger->logCall(__METHOD__, array('keyword' => $keyword, 'translation' => $translation, 'useAlwaysAvailable' => $useAlwaysAvailable));

        return $this->tagsHandler->getTagsByKeywordCount($keyword, $translation, $useAlwaysAvailable);
    }

    public function searchTags($searchString, $translation, $useAlwaysAvailable = true, $offset = 0, $limit = -1)
    {
        $this->logger->logCall(__METHOD__, array('searchString' => $searchString, 'translation' => $translation, 'useAlwaysAvailable' => $useAlwaysAvailable));

        return $this->tagsHandler->searchTags($searchString, $translation, $useAlwaysAvailable, $offset, $limit);
    }

    public function loadSynonyms($tagId, $offset = 0, $limit = -1, array $translations = null, $useAlwaysAvailable = true)
    {
        // Method caches all synonyms in cache and only uses offset / limit to slice the cached result
        $translationsKey = empty($translations) ? self::ALL_TRANSLATIONS_KEY : implode('|', $translations);
        $alwaysAvailableKey = $useAlwaysAvailable ? '1' : '0';

        $cache = $this->cache->getItem('tag', 'synonyms', $tagId, $translationsKey, $alwaysAvailableKey);
        $synonymIds = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('tag' => $tagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable));
            $synonyms = $this->tagsHandler->loadSynonyms($tagId, 0, null, $translations, $useAlwaysAvailable);

            $synonymIds = array();
            foreach ($synonyms as $synonym) {
                $synonymIds[] = $synonym->id;
            }

            $cache->set($synonymIds)->save();
        } else {
            $synonyms = array();
            foreach ($synonymIds as $synonymId) {
                $synonyms[] = $this->load($synonymId);
            }
        }

        return array_slice($synonyms, $offset, $limit > -1 ? $limit : null);
    }

    public function getSynonymCount($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        $this->logger->logCall(__METHOD__, array('tag' => $tagId, 'translations' => $translations, 'useAlwaysAvailable' => $useAlwaysAvailable));

        return $this->tagsHandler->getSynonymCount($tagId, $translations, $useAlwaysAvailable);
    }

    public function create(CreateStruct $createStruct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $createStruct));
        $tag = $this->tagsHandler->create($createStruct);

        $this->cache->getItem('tag', $tag->id)->set($tag)->save();

        return $tag;
    }

    public function update(UpdateStruct $updateStruct, $tagId)
    {
        $this->logger->logCall(__METHOD__, array('tag' => $tagId, 'struct', $updateStruct));
        $updatedTag = $this->tagsHandler->update($updateStruct, $tagId);

        $this->clearTagCache($tagId);

        return $updatedTag;
    }

    public function addSynonym(SynonymCreateStruct $createStruct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $createStruct));
        $synonym = $this->tagsHandler->addSynonym($createStruct);

        $this->clearTagCache($createStruct->mainTagId);

        $this->cache->getItem('tag', $synonym->id)->set($synonym)->save();

        return $synonym;
    }

    public function convertToSynonym($tagId, $mainTagId)
    {
        $this->logger->logCall(__METHOD__, array('tag' => $tagId));
        $synonym = $this->tagsHandler->convertToSynonym($tagId, $mainTagId);

        $this->clearTagCache($tagId);
        $this->clearTagCache($mainTagId);

        return $synonym;
    }

    public function merge($tagId, $targetTagId)
    {
        $this->logger->logCall(__METHOD__, array('tag' => $tagId, 'targetTag' => $targetTagId));

        $this->tagsHandler->merge($tagId, $targetTagId);

        $this->cache->clear('tag'); //TIMBER!
    }

    public function copySubtree($sourceId, $destinationParentId)
    {
        $this->logger->logCall(__METHOD__, array('sourceTag' => $sourceId, 'destinationTag' => $destinationParentId));

        $return = $this->tagsHandler->copySubtree($sourceId, $destinationParentId);

        $this->cache->clear('tag'); //TIMBER!

        return $return;
    }

    public function moveSubtree($sourceId, $destinationParentId)
    {
        $this->logger->logCall(__METHOD__, array('sourceTag' => $sourceId, 'destinationTag' => $destinationParentId));

        $return = $this->tagsHandler->moveSubtree($sourceId, $destinationParentId);

        $this->cache->clear('tag'); //TIMBER!

        return $return;
    }

    public function deleteTag($tagId)
    {
        $this->logger->logCall(__METHOD__, array('tag' => $tagId));
        $this->tagsHandler->deleteTag($tagId);

        $this->cache->clear('tag'); //TIMBER!
    }

    protected function clearTagCache($tagIdToClear)
    {
        $tagIds = array($tagIdToClear);

        foreach ($this->loadSynonyms($tagIdToClear) as $synonym) {
            $tagIds[] = $synonym->id;
        }

        foreach ($tagIds as $tagId) {
            $this->cache->clear('tag', $tagId);
            $this->cache->clear('tag', 'info', $tagId);

            $this->cache->clear('tag', 'remoteId', $tagId);
            $this->cache->clear('tag', 'info', 'remoteId', $tagId);
        }

        $this->cache->clear('tag', 'synonyms', $tagIdToClear);
    }
}
