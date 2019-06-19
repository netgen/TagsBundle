<?php

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Tags;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\Handler as BaseTagsHandler;
use Netgen\TagsBundle\SPI\Persistence\Tags\SearchResult;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;

class Handler implements BaseTagsHandler
{
    /**
     * @var \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway
     */
    private $gateway;

    /**
     * @var \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper
     */
    private $mapper;

    public function __construct(Gateway $gateway, Mapper $mapper)
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
    }

    public function load($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        $rows = $this->gateway->getFullTagData($tagId, $translations, $useAlwaysAvailable);
        if (count($rows) === 0) {
            throw new NotFoundException('tag', $tagId);
        }

        $tag = $this->mapper->extractTagListFromRows($rows);

        return reset($tag);
    }

    public function loadList(array $tagIds, array $translations = null, $useAlwaysAvailable = true)
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

    public function loadTagInfo($tagId)
    {
        $row = $this->gateway->getBasicTagData($tagId);

        return $this->mapper->createTagInfoFromRow($row);
    }

    public function loadByRemoteId($remoteId, array $translations = null, $useAlwaysAvailable = true)
    {
        $rows = $this->gateway->getFullTagDataByRemoteId($remoteId, $translations, $useAlwaysAvailable);
        if (count($rows) === 0) {
            throw new NotFoundException('tag', $remoteId);
        }

        $tag = $this->mapper->extractTagListFromRows($rows);

        return reset($tag);
    }

    public function loadTagInfoByRemoteId($remoteId)
    {
        $row = $this->gateway->getBasicTagDataByRemoteId($remoteId);

        return $this->mapper->createTagInfoFromRow($row);
    }

    public function loadTagByKeywordAndParentId($keyword, $parentTagId, array $translations = null, $useAlwaysAvailable = true)
    {
        $rows = $this->gateway->getFullTagDataByKeywordAndParentId($keyword, $parentTagId, $translations, $useAlwaysAvailable);
        if (count($rows) === 0) {
            throw new NotFoundException('tag', $keyword);
        }

        $tag = $this->mapper->extractTagListFromRows($rows);

        return reset($tag);
    }

    public function loadChildren($tagId, $offset = 0, $limit = -1, array $translations = null, $useAlwaysAvailable = true)
    {
        $tags = $this->gateway->getChildren($tagId, $offset, $limit, $translations, $useAlwaysAvailable);

        return $this->mapper->extractTagListFromRows($tags);
    }

    public function getChildrenCount($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        return $this->gateway->getChildrenCount($tagId, $translations, $useAlwaysAvailable);
    }

    public function loadTagsByKeyword($keyword, $translation, $useAlwaysAvailable = true, $offset = 0, $limit = -1)
    {
        $tags = $this->gateway->getTagsByKeyword($keyword, $translation, $useAlwaysAvailable, true, $offset, $limit);

        return $this->mapper->extractTagListFromRows($tags);
    }

    public function getTagsByKeywordCount($keyword, $translation, $useAlwaysAvailable = true)
    {
        return $this->gateway->getTagsByKeywordCount($keyword, $translation, $useAlwaysAvailable, true);
    }

    public function searchTags($searchString, $translation, $useAlwaysAvailable = true, $offset = 0, $limit = -1)
    {
        $tags = $this->gateway->getTagsByKeyword($searchString, $translation, $useAlwaysAvailable, false, $offset, $limit);
        $totalCount = $this->gateway->getTagsByKeywordCount($searchString, $translation, $useAlwaysAvailable, false);

        return new SearchResult(
            [
                'tags' => $this->mapper->extractTagListFromRows($tags),
                'totalCount' => $totalCount,
            ]
        );
    }

    public function loadSynonyms($tagId, $offset = 0, $limit = -1, array $translations = null, $useAlwaysAvailable = true)
    {
        $tags = $this->gateway->getSynonyms($tagId, $offset, $limit, $translations, $useAlwaysAvailable);

        return $this->mapper->extractTagListFromRows($tags);
    }

    public function getSynonymCount($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        return $this->gateway->getSynonymCount($tagId, $translations, $useAlwaysAvailable);
    }

    public function create(CreateStruct $createStruct)
    {
        $parentTagData = null;
        if ($createStruct->parentTagId > 0) {
            $parentTagData = $this->gateway->getBasicTagData($createStruct->parentTagId);
        }

        $newTagId = $this->gateway->create($createStruct, $parentTagData);

        return $this->load($newTagId);
    }

    public function update(UpdateStruct $updateStruct, $tagId)
    {
        $this->gateway->update($updateStruct, $tagId);

        return $this->load($tagId);
    }

    public function addSynonym(SynonymCreateStruct $createStruct)
    {
        $mainTagData = $this->gateway->getBasicTagData($createStruct->mainTagId);
        $newSynonymId = $this->gateway->createSynonym($createStruct, $mainTagData);

        return $this->load($newSynonymId);
    }

    public function convertToSynonym($tagId, $mainTagId)
    {
        $tagInfo = $this->loadTagInfo($tagId);
        $mainTagData = $this->gateway->getBasicTagData($mainTagId);

        foreach ($this->loadSynonyms($tagId) as $synonym) {
            $this->gateway->moveSynonym($synonym->id, $mainTagData);
        }

        $this->gateway->convertToSynonym($tagInfo->id, $mainTagData);

        return $this->load($tagId);
    }

    public function merge($tagId, $targetTagId)
    {
        foreach ($this->loadSynonyms($tagId) as $synonym) {
            $this->gateway->transferTagAttributeLinks($synonym->id, $targetTagId);
            $this->gateway->deleteTag($synonym->id);
        }

        $this->gateway->transferTagAttributeLinks($tagId, $targetTagId);
        $this->gateway->deleteTag($tagId);
    }

    public function copySubtree($sourceId, $destinationParentId)
    {
        $sourceTag = $this->load($sourceId);

        $copiedTagId = $this->recursiveCopySubtree($sourceTag, $destinationParentId);

        return $this->load($copiedTagId);
    }

    public function moveSubtree($sourceId, $destinationParentId)
    {
        $sourceTagData = $this->gateway->getBasicTagData($sourceId);

        $destinationParentTagData = null;
        if ($destinationParentId > 0) {
            $destinationParentTagData = $this->gateway->getBasicTagData($destinationParentId);
        }

        $this->gateway->moveSubtree($sourceTagData, $destinationParentTagData);

        return $this->load($sourceId);
    }

    public function deleteTag($tagId)
    {
        $tagInfo = $this->loadTagInfo($tagId);
        $this->gateway->deleteTag($tagInfo->id);
    }

    /**
     * Copies tag object identified by $sourceData into destination identified by $destinationParentData.
     *
     * Also performs a copy of all child locations of $sourceData tag
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\Tag $sourceTag The subtree denoted by the tag to copy
     * @param int $destinationParentTagId The target parent tag ID for the copy operation
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The newly created tag of the copied subtree
     */
    private function recursiveCopySubtree(Tag $sourceTag, $destinationParentTagId)
    {
        // First copy the root node

        $createStruct = new CreateStruct();
        $createStruct->parentTagId = $destinationParentTagId;
        $createStruct->keywords = $sourceTag->keywords;
        $createStruct->remoteId = md5(uniqid(get_class($this), true));
        $createStruct->alwaysAvailable = $sourceTag->alwaysAvailable;
        $createStruct->mainLanguageCode = $sourceTag->mainLanguageCode;

        $createdTag = $this->create($createStruct);
        foreach ($this->loadSynonyms($sourceTag->id) as $synonym) {
            $synonymCreateStruct = new SynonymCreateStruct();
            $synonymCreateStruct->keywords = $synonym->keywords;
            $synonymCreateStruct->remoteId = md5(uniqid(get_class($this), true));
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
                $createdTag->id
            );
        }

        return $createdTag->id;
    }
}
