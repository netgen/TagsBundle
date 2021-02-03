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
    protected $gateway;

    /**
     * @var \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper
     */
    protected $mapper;

    /**
     * @param \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway $gateway
     * @param \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper $mapper
     */
    public function __construct(Gateway $gateway, Mapper $mapper)
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
    }

    /**
     * Loads a tag object from its $tagId.
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @param mixed $tagId
     * @param string[] $translations
     * @param bool $useAlwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function load($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        $rows = $this->gateway->getFullTagData($tagId, $translations, $useAlwaysAvailable);
        if (empty($rows)) {
            throw new NotFoundException('tag', $tagId);
        }

        $tag = $this->mapper->extractTagListFromRows($rows);

        return reset($tag);
    }

    /**
     * {@inheritdoc}
     */
    public function loadList(array $tagIds, array $translations = null, $useAlwaysAvailable = true)
    {
        // TODO: This can be optimized in the future by adding method on gateway to load several
        $tags = [];
        foreach ($tagIds as $tagId) {
            $rows = $this->gateway->getFullTagData($tagId, $translations, $useAlwaysAvailable);
            if (!empty($rows)) {
                $tags[(int) $tagId] = $this->mapper->extractTagListFromRows($rows)[0];
            }
        }

        return $tags;
    }

    /**
     * Loads a tag info object from its $tagId.
     *
     * @param mixed $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo
     */
    public function loadTagInfo($tagId)
    {
        $row = $this->gateway->getBasicTagData($tagId);

        return $this->mapper->createTagInfoFromRow($row);
    }

    /**
     * Loads a tag object from its $remoteId.
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @param string $remoteId
     * @param string[] $translations
     * @param bool $useAlwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function loadByRemoteId($remoteId, array $translations = null, $useAlwaysAvailable = true)
    {
        $rows = $this->gateway->getFullTagDataByRemoteId($remoteId, $translations, $useAlwaysAvailable);
        if (empty($rows)) {
            throw new NotFoundException('tag', $remoteId);
        }

        $tag = $this->mapper->extractTagListFromRows($rows);

        return reset($tag);
    }

    /**
     * Loads a tag info object from its remote ID.
     *
     * @param string $remoteId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo
     */
    public function loadTagInfoByRemoteId($remoteId)
    {
        $row = $this->gateway->getBasicTagDataByRemoteId($remoteId);

        return $this->mapper->createTagInfoFromRow($row);
    }

    /**
     * Loads tags by specified keyword and parent ID.
     *
     * @param string $keyword The keyword to fetch tag for
     * @param mixed $parentTagId The parent ID to fetch tag for
     * @param string[] $translations The languages to load
     * @param bool $useAlwaysAvailable Check for main language if true (default) and if tag is always available
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTagByKeywordAndParentId($keyword, $parentTagId, array $translations = null, $useAlwaysAvailable = true)
    {
        $rows = $this->gateway->getFullTagDataByKeywordAndParentId($keyword, $parentTagId, $translations, $useAlwaysAvailable);
        if (empty($rows)) {
            throw new NotFoundException('tag', $keyword);
        }

        $tag = $this->mapper->extractTagListFromRows($rows);

        return reset($tag);
    }

    /**
     * Loads children of a tag identified by $tagId.
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all children starting at $offset are returned
     * @param string[] $translations
     * @param bool $useAlwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function loadChildren($tagId, $offset = 0, $limit = -1, array $translations = null, $useAlwaysAvailable = true)
    {
        $tags = $this->gateway->getChildren($tagId, $offset, $limit, $translations, $useAlwaysAvailable);

        return $this->mapper->extractTagListFromRows($tags);
    }

    /**
     * Returns the number of children of a tag identified by $tagId.
     *
     * @param mixed $tagId
     * @param string[] $translations
     * @param bool $useAlwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return int
     */
    public function getChildrenCount($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        return $this->gateway->getChildrenCount($tagId, $translations, $useAlwaysAvailable);
    }

    /**
     * Loads tags with specified $keyword.
     *
     * @param string $keyword
     * @param string $translation
     * @param bool $useAlwaysAvailable
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all tags starting at $offset are returned
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function loadTagsByKeyword($keyword, $translation, $useAlwaysAvailable = true, $offset = 0, $limit = -1)
    {
        $tags = $this->gateway->getTagsByKeyword($keyword, $translation, $useAlwaysAvailable, true, $offset, $limit);

        return $this->mapper->extractTagListFromRows($tags);
    }

    /**
     * Returns the number of tags with specified $keyword.
     *
     * @param string $keyword
     * @param string $translation
     * @param bool $useAlwaysAvailable
     *
     * @return int
     */
    public function getTagsByKeywordCount($keyword, $translation, $useAlwaysAvailable = true)
    {
        return $this->gateway->getTagsByKeywordCount($keyword, $translation, $useAlwaysAvailable, true);
    }

    /**
     * Searches for tags.
     *
     * @param string $searchString
     * @param string $translation
     * @param bool $useAlwaysAvailable
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all tags starting at $offset are returned
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\SearchResult
     */
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

    /**
     * Loads the synonyms of a tag identified by $tagId.
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all synonyms starting at $offset are returned
     * @param string[] $translations
     * @param bool $useAlwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function loadSynonyms($tagId, $offset = 0, $limit = -1, array $translations = null, $useAlwaysAvailable = true)
    {
        $tags = $this->gateway->getSynonyms($tagId, $offset, $limit, $translations, $useAlwaysAvailable);

        return $this->mapper->extractTagListFromRows($tags);
    }

    /**
     * Returns the number of synonyms of a tag identified by $tagId.
     *
     * @param mixed $tagId
     * @param string[] $translations
     * @param bool $useAlwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return int
     */
    public function getSynonymCount($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        return $this->gateway->getSynonymCount($tagId, $translations, $useAlwaysAvailable);
    }

    /**
     * Creates the new tag.
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct $createStruct
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The newly created tag
     */
    public function create(CreateStruct $createStruct)
    {
        $parentTagData = null;
        if (!empty($createStruct->parentTagId)) {
            $parentTagData = $this->gateway->getBasicTagData($createStruct->parentTagId);
        }

        $newTagId = $this->gateway->create($createStruct, $parentTagData);

        return $this->load($newTagId);
    }

    /**
     * Updates tag identified by $tagId.
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct $updateStruct
     * @param mixed $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The updated tag
     */
    public function update(UpdateStruct $updateStruct, $tagId)
    {
        $this->gateway->update($updateStruct, $tagId);

        return $this->load($tagId);
    }

    /**
     * Creates a synonym.
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct $createStruct
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The created synonym
     */
    public function addSynonym(SynonymCreateStruct $createStruct)
    {
        $mainTagData = $this->gateway->getBasicTagData($createStruct->mainTagId);
        $newSynonymId = $this->gateway->createSynonym($createStruct, $mainTagData);

        return $this->load($newSynonymId);
    }

    /**
     * Converts tag identified by $tagId to a synonym of tag identified by $mainTagId.
     *
     * @param mixed $tagId
     * @param mixed $mainTagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $tagId or $mainTagId are invalid
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The converted synonym
     */
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

    /**
     * Merges the tag identified by $tagId into the tag identified by $targetTagId.
     *
     * @param mixed $tagId
     * @param mixed $targetTagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $tagId or $targetTagId are invalid
     */
    public function merge($tagId, $targetTagId)
    {
        foreach ($this->loadSynonyms($tagId) as $synonym) {
            $this->gateway->transferTagAttributeLinks($synonym->id, $targetTagId);
            $this->gateway->deleteTag($synonym->id);
        }

        $this->gateway->transferTagAttributeLinks($tagId, $targetTagId);
        $this->gateway->deleteTag($tagId);
    }

    /**
     * Copies tag object identified by $sourceId into destination identified by $destinationParentId.
     *
     * Also performs a copy of all child locations of $sourceId tag
     *
     * @param mixed $sourceId The subtree denoted by the tag to copy
     * @param mixed $destinationParentId The target parent tag for the copy operation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $sourceId or $destinationParentId are invalid
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The newly created tag of the copied subtree
     */
    public function copySubtree($sourceId, $destinationParentId)
    {
        $sourceTag = $this->load($sourceId);

        $copiedTagId = $this->recursiveCopySubtree($sourceTag, $destinationParentId);

        return $this->load($copiedTagId);
    }

    /**
     * Moves a tag identified by $sourceId into new parent identified by $destinationParentId.
     *
     * @param mixed $sourceId
     * @param mixed $destinationParentId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $sourceId or $destinationParentId are invalid
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The updated root tag of the moved subtree
     */
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

    /**
     * Deletes tag identified by $tagId, including its synonyms and all tags under it.
     *
     * @param mixed $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * If $tagId is a synonym, only the synonym is deleted
     */
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
    protected function recursiveCopySubtree(Tag $sourceTag, $destinationParentTagId)
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
                $createdTag->id
            );
        }

        return $createdTag->id;
    }
}
