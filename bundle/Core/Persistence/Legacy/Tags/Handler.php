<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Tags;

use Ibexa\Core\Base\Exceptions\NotFoundException;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\Handler as BaseTagsHandler;
use Netgen\TagsBundle\SPI\Persistence\Tags\SearchResult;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag;
use Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;

use function count;
use function md5;
use function reset;
use function uniqid;

/**
 * @final
 */
class Handler implements BaseTagsHandler
{
    public function __construct(private Gateway $gateway, private Mapper $mapper) {}

    public function load(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): Tag
    {
        $rows = $this->gateway->getFullTagData($tagId, $translations, $useAlwaysAvailable);
        if (count($rows) === 0) {
            throw new NotFoundException('tag', $tagId);
        }

        $tag = $this->mapper->extractTagListFromRows($rows);

        return reset($tag);
    }

    public function loadList(array $tagIds, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        // TODO: This can be optimized in the future by adding method on gateway to load several
        $tags = [];
        foreach ($tagIds as $tagId) {
            $rows = $this->gateway->getFullTagData($tagId, $translations, $useAlwaysAvailable);
            if (count($rows) > 0) {
                $tags[(int) $tagId] = $this->mapper->extractTagListFromRows($rows)[0];
            }
        }

        return $tags;
    }

    public function loadTagInfo(int $tagId): TagInfo
    {
        $row = $this->gateway->getBasicTagData($tagId);

        return $this->mapper->createTagInfoFromRow($row);
    }

    public function loadByRemoteId(string $remoteId, ?array $translations = null, bool $useAlwaysAvailable = true): Tag
    {
        $rows = $this->gateway->getFullTagDataByRemoteId($remoteId, $translations, $useAlwaysAvailable);
        if (count($rows) === 0) {
            throw new NotFoundException('tag', $remoteId);
        }

        $tag = $this->mapper->extractTagListFromRows($rows);

        return reset($tag);
    }

    public function loadTagInfoByRemoteId(string $remoteId): TagInfo
    {
        $row = $this->gateway->getBasicTagDataByRemoteId($remoteId);

        return $this->mapper->createTagInfoFromRow($row);
    }

    public function loadTagByKeywordAndParentId(string $keyword, int $parentTagId, ?array $translations = null, bool $useAlwaysAvailable = true): Tag
    {
        $rows = $this->gateway->getFullTagDataByKeywordAndParentId($keyword, $parentTagId, $translations, $useAlwaysAvailable);
        if (count($rows) === 0) {
            throw new NotFoundException('tag', $keyword);
        }

        $tag = $this->mapper->extractTagListFromRows($rows);

        return reset($tag);
    }

    public function loadChildren(int $tagId, int $offset = 0, int $limit = -1, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        $tags = $this->gateway->getChildren($tagId, $offset, $limit, $translations, $useAlwaysAvailable);

        return $this->mapper->extractTagListFromRows($tags);
    }

    public function getChildrenCount(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): int
    {
        return $this->gateway->getChildrenCount($tagId, $translations, $useAlwaysAvailable);
    }

    public function loadTagsByKeyword(string $keyword, string $translation, bool $useAlwaysAvailable = true, int $offset = 0, int $limit = -1): array
    {
        $tags = $this->gateway->getTagsByKeyword($keyword, $translation, $useAlwaysAvailable, true, $offset, $limit);

        return $this->mapper->extractTagListFromRows($tags);
    }

    public function getTagsByKeywordCount(string $keyword, string $translation, bool $useAlwaysAvailable = true): int
    {
        return $this->gateway->getTagsByKeywordCount($keyword, $translation, $useAlwaysAvailable);
    }

    public function searchTags(string $searchString, string $translation, bool $useAlwaysAvailable = true, int $offset = 0, int $limit = -1): SearchResult
    {
        $tags = $this->gateway->getTagsByKeyword($searchString, $translation, $useAlwaysAvailable, false, $offset, $limit);
        $totalCount = $this->gateway->getTagsByKeywordCount($searchString, $translation, $useAlwaysAvailable, false);

        return new SearchResult(
            [
                'tags' => $this->mapper->extractTagListFromRows($tags),
                'totalCount' => $totalCount,
            ],
        );
    }

    public function loadSynonyms(int $tagId, int $offset = 0, int $limit = -1, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        $tags = $this->gateway->getSynonyms($tagId, $offset, $limit, $translations, $useAlwaysAvailable);

        return $this->mapper->extractTagListFromRows($tags);
    }

    public function getSynonymCount(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): int
    {
        return $this->gateway->getSynonymCount($tagId, $translations, $useAlwaysAvailable);
    }

    public function create(CreateStruct $createStruct): Tag
    {
        $parentTagData = null;
        if ($createStruct->parentTagId > 0) {
            $parentTagData = $this->gateway->getBasicTagData($createStruct->parentTagId);
        }

        $newTagId = $this->gateway->create($createStruct, $parentTagData);

        return $this->load($newTagId);
    }

    public function update(UpdateStruct $updateStruct, int $tagId): Tag
    {
        $this->gateway->update($updateStruct, $tagId);

        return $this->load($tagId);
    }

    public function addSynonym(SynonymCreateStruct $createStruct): Tag
    {
        $mainTagData = $this->gateway->getBasicTagData($createStruct->mainTagId);
        $newSynonymId = $this->gateway->createSynonym($createStruct, $mainTagData);

        return $this->load($newSynonymId);
    }

    public function convertToSynonym(int $tagId, int $mainTagId): Tag
    {
        $tagInfo = $this->loadTagInfo($tagId);
        $mainTagData = $this->gateway->getBasicTagData($mainTagId);

        foreach ($this->loadSynonyms($tagId) as $synonym) {
            $this->gateway->moveSynonym($synonym->id, $mainTagData);
        }

        $this->gateway->convertToSynonym($tagInfo->id, $mainTagData);

        return $this->load($tagId);
    }

    public function merge(int $tagId, int $targetTagId): void
    {
        foreach ($this->loadSynonyms($tagId) as $synonym) {
            $this->gateway->transferTagAttributeLinks($synonym->id, $targetTagId);
            $this->gateway->deleteTag($synonym->id);
        }

        $this->gateway->transferTagAttributeLinks($tagId, $targetTagId);
        $this->gateway->deleteTag($tagId);
    }

    public function copySubtree(int $sourceId, int $destinationParentId): Tag
    {
        $sourceTag = $this->load($sourceId);

        $copiedTagId = $this->recursiveCopySubtree($sourceTag, $destinationParentId);

        return $this->load($copiedTagId);
    }

    public function moveSubtree(int $sourceId, int $destinationParentId): Tag
    {
        $sourceTagData = $this->gateway->getBasicTagData($sourceId);

        $destinationParentTagData = null;
        if ($destinationParentId > 0) {
            $destinationParentTagData = $this->gateway->getBasicTagData($destinationParentId);
        }

        $this->gateway->moveSubtree($sourceTagData, $destinationParentTagData);

        return $this->load($sourceId);
    }

    public function deleteTag(int $tagId): void
    {
        $tagInfo = $this->loadTagInfo($tagId);
        $this->gateway->deleteTag($tagInfo->id);
    }

    public function hideTag(int $tagId): void
    {
        $tagInfo = $this->loadTagInfo($tagId);
        $this->gateway->hideTag($tagInfo->id);
    }

    public function revealTag(int $tagId): void
    {
        $tagInfo = $this->loadTagInfo($tagId);
        $this->gateway->revealTag($tagInfo->id);
    }

    /**
     * Copies tag object identified by $sourceData into destination identified by $destinationParentData.
     *
     * Also performs a copy of all child locations of $sourceData tag
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\Tag $sourceTag The subtree denoted by the tag to copy
     * @param int $destinationParentTagId The target parent tag ID for the copy operation
     *
     * @return int The ID of the newly created tag of the copied subtree
     */
    private function recursiveCopySubtree(Tag $sourceTag, int $destinationParentTagId): int
    {
        // First copy the root node

        $createStruct = new CreateStruct();
        $createStruct->parentTagId = $destinationParentTagId;
        $createStruct->keywords = $sourceTag->keywords;
        $createStruct->remoteId = md5(uniqid(static::class, true));
        $createStruct->alwaysAvailable = $sourceTag->alwaysAvailable;
        $createStruct->mainLanguageCode = $sourceTag->mainLanguageCode;

        $createdTag = $this->create($createStruct);
        foreach ($this->loadSynonyms($sourceTag->id) as $synonym) {
            $synonymCreateStruct = new SynonymCreateStruct();
            $synonymCreateStruct->keywords = $synonym->keywords;
            $synonymCreateStruct->remoteId = md5(uniqid(static::class, true));
            $synonymCreateStruct->mainTagId = $createdTag->id;
            $synonymCreateStruct->alwaysAvailable = $synonym->alwaysAvailable;
            $synonymCreateStruct->mainLanguageCode = $synonym->mainLanguageCode;

            $this->addSynonym($synonymCreateStruct);
        }

        // Then copy the children
        $children = $this->loadChildren($sourceTag->id);
        foreach ($children as $child) {
            $this->recursiveCopySubtree(
                $child,
                $createdTag->id,
            );
        }

        return $createdTag->id;
    }
}
