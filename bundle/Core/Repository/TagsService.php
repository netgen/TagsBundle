<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Repository;

use DateTimeImmutable;
use Exception;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as BaseNotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use Netgen\TagsBundle\API\Repository\TagsService as TagsServiceInterface;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId;
use Netgen\TagsBundle\API\Repository\Values\Tags\SearchResult;
use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\Handler as TagsHandler;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct as SPISynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag as SPITag;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;

/**
 * @final
 */
class TagsService implements TagsServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \Netgen\TagsBundle\SPI\Persistence\Tags\Handler
     */
    private $tagsHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    private $languageHandler;

    /**
     * Counter for the current sudo nesting level.
     *
     * @var int
     */
    private $sudoNestingLevel = 0;

    public function __construct(
        Repository $repository,
        TagsHandler $tagsHandler,
        LanguageHandler $languageHandler
    ) {
        $this->repository = $repository;
        $this->tagsHandler = $tagsHandler;
        $this->languageHandler = $languageHandler;
    }

    public function loadTag(int $tagId, ?array $languages = null, bool $useAlwaysAvailable = true): Tag
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        $spiTag = $this->tagsHandler->load(
            $tagId,
            $languages,
            $useAlwaysAvailable
        );

        return $this->buildTagDomainObject($spiTag);
    }

    public function loadTagList(array $tagIds, ?array $languages = null, bool $useAlwaysAvailable = true): array
    {
        if ($this->hasAccess('tags', 'read') === false) {
            return [];
        }

        $spiTags = $this->tagsHandler->loadList(
            $tagIds,
            $languages,
            $useAlwaysAvailable
        );

        return $this->buildTagDomainList($spiTags);
    }

    public function loadTagByRemoteId(string $remoteId, ?array $languages = null, bool $useAlwaysAvailable = true): Tag
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        $spiTag = $this->tagsHandler->loadByRemoteId(
            $remoteId,
            $languages,
            $useAlwaysAvailable
        );

        return $this->buildTagDomainObject($spiTag);
    }

    public function loadTagByUrl(string $url, array $languages): Tag
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        $keywordArray = explode('/', trim($url, '/'));
        if (!is_array($keywordArray) || count($keywordArray) === 0) {
            throw new InvalidArgumentValue('url', $url);
        }

        $parentId = 0;
        $spiTag = null;

        if (count($languages) > 0) {
            foreach ($keywordArray as $keyword) {
                if ($keyword === '') {
                    continue;
                }

                $spiTag = $this->tagsHandler->loadTagByKeywordAndParentId($keyword, $parentId, $languages);

                // Reasoning behind this is that the FIRST item sorted by languages must be matched to the keyword
                // If not, it means that the tag is not translated to the correct keyword in the most prioritized language
                $spiTagKeywords = [];
                foreach ($languages as $language) {
                    if (isset($spiTag->keywords[$language])) {
                        $spiTagKeywords[$language] = $spiTag->keywords[$language];
                    }
                }

                if ($spiTag->alwaysAvailable) {
                    if (!isset($spiTagKeywords[$spiTag->mainLanguageCode]) && isset($spiTag->keywords[$spiTag->mainLanguageCode])) {
                        $spiTagKeywords[$spiTag->mainLanguageCode] = $spiTag->keywords[$spiTag->mainLanguageCode];
                    }
                }

                $spiTagKeywords = array_values($spiTagKeywords);
                if (count($spiTagKeywords) > 0 && $spiTagKeywords[0] !== $keyword) {
                    throw new BaseNotFoundException('tag', $url);
                }

                $parentId = $spiTag->id;
            }
        }

        if (!$spiTag instanceof SPITag) {
            throw new BaseNotFoundException('tag', $url);
        }

        return $this->buildTagDomainObject($spiTag);
    }

    public function loadTagChildren(?Tag $tag = null, int $offset = 0, int $limit = -1, ?array $languages = null, bool $useAlwaysAvailable = true): array
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        $spiTags = $this->tagsHandler->loadChildren(
            $tag !== null ? $tag->id : 0,
            $offset,
            $limit,
            $languages,
            $useAlwaysAvailable
        );

        $tags = [];
        foreach ($spiTags as $spiTag) {
            $tags[] = $this->buildTagDomainObject($spiTag);
        }

        return $tags;
    }

    public function getTagChildrenCount(?Tag $tag = null, ?array $languages = null, bool $useAlwaysAvailable = true): int
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        return $this->tagsHandler->getChildrenCount(
            $tag !== null ? $tag->id : 0,
            $languages,
            $useAlwaysAvailable
        );
    }

    public function loadTagsByKeyword(string $keyword, string $language, bool $useAlwaysAvailable = true, int $offset = 0, int $limit = -1): array
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        $spiTags = $this->tagsHandler->loadTagsByKeyword($keyword, $language, $useAlwaysAvailable, $offset, $limit);

        $tags = [];
        foreach ($spiTags as $spiTag) {
            $tags[] = $this->buildTagDomainObject($spiTag);
        }

        return $tags;
    }

    public function getTagsByKeywordCount(string $keyword, string $language, bool $useAlwaysAvailable = true): int
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        return $this->tagsHandler->getTagsByKeywordCount($keyword, $language, $useAlwaysAvailable);
    }

    public function searchTags(string $searchString, string $language, bool $useAlwaysAvailable = true, int $offset = 0, int $limit = -1): SearchResult
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        $spiSearchResult = $this->tagsHandler->searchTags(
            $searchString,
            $language,
            $useAlwaysAvailable,
            $offset,
            $limit
        );

        $tags = [];
        foreach ($spiSearchResult->tags as $spiTag) {
            $tags[] = $this->buildTagDomainObject($spiTag);
        }

        return new SearchResult(
            [
                'tags' => $tags,
                'totalCount' => $spiSearchResult->totalCount,
            ]
        );
    }

    public function loadTagSynonyms(Tag $tag, int $offset = 0, int $limit = -1, ?array $languages = null, bool $useAlwaysAvailable = true): array
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        if ($tag->mainTagId > 0) {
            throw new InvalidArgumentException('tag', 'Tag is a synonym');
        }

        $spiTags = $this->tagsHandler->loadSynonyms(
            $tag->id,
            $offset,
            $limit,
            $languages,
            $useAlwaysAvailable
        );

        $tags = [];
        foreach ($spiTags as $spiTag) {
            $tags[] = $this->buildTagDomainObject($spiTag);
        }

        return $tags;
    }

    public function getTagSynonymCount(Tag $tag, ?array $languages = null, bool $useAlwaysAvailable = true): int
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        if ($tag->mainTagId > 0) {
            throw new InvalidArgumentException('tag', 'Tag is a synonym');
        }

        return $this->tagsHandler->getSynonymCount(
            $tag->id,
            $languages,
            $useAlwaysAvailable
        );
    }

    public function getRelatedContent(Tag $tag, int $offset = 0, int $limit = -1, bool $returnContentInfo = true, array $additionalCriteria = [], array $sortClauses = []): array
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        $method = 'findContent';
        if ($returnContentInfo) {
            $method = 'findContentInfo';
        }

        $criteria = [new TagId($tag->id)];
        $filter = new Criterion\LogicalAnd(array_merge($criteria, $additionalCriteria));

        if (count($sortClauses) === 0) {
            $sortClauses = [
                new Query\SortClause\DateModified(Query::SORT_DESC),
            ];
        }

        $searchResult = $this->repository->getSearchService()->{$method}(
            new Query(
                [
                    'offset' => $offset,
                    'limit' => $limit > 0 ? $limit : 1000000,
                    'filter' => $filter,
                    'sortClauses' => $sortClauses,
                ]
            )
        );

        $content = [];
        foreach ($searchResult->searchHits as $searchHit) {
            $content[] = $searchHit->valueObject;
        }

        return $content;
    }

    public function getRelatedContentCount(Tag $tag, array $additionalCriteria = []): int
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        $criteria = [new TagId($tag->id)];
        $filter = new Criterion\LogicalAnd(array_merge($criteria, $additionalCriteria));

        $searchResult = $this->repository->getSearchService()->findContentInfo(
            new Query(
                [
                    'limit' => 0,
                    'filter' => $filter,
                ]
            )
        );

        return $searchResult->totalCount;
    }

    public function createTag(TagCreateStruct $tagCreateStruct): Tag
    {
        $keywords = $tagCreateStruct->getKeywords();

        if ($tagCreateStruct->parentTagId > 0) {
            if ($this->canUser('tags', 'add', $this->loadTag($tagCreateStruct->parentTagId)) !== true) {
                throw new UnauthorizedException('tags', 'add');
            }
        } elseif ($this->hasAccess('tags', 'add') === false) {
            throw new UnauthorizedException('tags', 'add');
        }

        if (!is_string($tagCreateStruct->mainLanguageCode) || $tagCreateStruct->mainLanguageCode === '') {
            throw new InvalidArgumentValue('mainLanguageCode', $tagCreateStruct->mainLanguageCode, 'TagCreateStruct');
        }

        if (!is_array($keywords) || count($keywords) === 0) {
            throw new InvalidArgumentValue('keywords', $keywords, 'TagCreateStruct');
        }

        if (!isset($keywords[$tagCreateStruct->mainLanguageCode])) {
            throw new InvalidArgumentValue('keywords', $keywords, 'TagCreateStruct');
        }

        if ($tagCreateStruct->remoteId !== null && (!is_string($tagCreateStruct->remoteId) || $tagCreateStruct->remoteId === '')) {
            throw new InvalidArgumentValue('remoteId', $tagCreateStruct->remoteId, 'TagCreateStruct');
        }

        // check for existence of tag with provided remote ID
        if ($tagCreateStruct->remoteId !== null) {
            try {
                $this->tagsHandler->loadTagInfoByRemoteId($tagCreateStruct->remoteId);

                throw new InvalidArgumentException('tagCreateStruct', 'Tag with provided remote ID already exists');
            } catch (NotFoundException $e) {
                // Do nothing
            }
        } else {
            $tagCreateStruct->remoteId = md5(uniqid(get_class($this), true));
        }

        if (!is_bool($tagCreateStruct->alwaysAvailable)) {
            throw new InvalidArgumentValue('alwaysAvailable', $tagCreateStruct->alwaysAvailable, 'TagCreateStruct');
        }

        $createStruct = new CreateStruct();
        $createStruct->parentTagId = $tagCreateStruct->parentTagId > 0 ? $tagCreateStruct->parentTagId : 0;
        $createStruct->mainLanguageCode = $tagCreateStruct->mainLanguageCode;
        $createStruct->keywords = $keywords;
        $createStruct->remoteId = $tagCreateStruct->remoteId;
        $createStruct->alwaysAvailable = $tagCreateStruct->alwaysAvailable;

        $this->repository->beginTransaction();

        try {
            $newTag = $this->tagsHandler->create($createStruct);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();

            throw $e;
        }

        return $this->buildTagDomainObject($newTag);
    }

    public function updateTag(Tag $tag, TagUpdateStruct $tagUpdateStruct): Tag
    {
        $keywords = $tagUpdateStruct->getKeywords();

        if ($tag->mainTagId > 0) {
            if ($this->hasAccess('tags', 'edit') === false) {
                throw new UnauthorizedException('tags', 'edit');
            }
        } elseif ($this->hasAccess('tags', 'editsynonym') === false) {
            throw new UnauthorizedException('tags', 'editsynonym');
        }

        if ($keywords !== null && (!is_array($keywords) || count($keywords) === 0)) {
            throw new InvalidArgumentValue('keywords', $keywords, 'TagUpdateStruct');
        }

        if ($keywords !== null) {
            foreach ($keywords as $keyword) {
                if (!is_string($keyword) || $keyword === '') {
                    throw new InvalidArgumentValue('keywords', $keywords, 'TagUpdateStruct');
                }
            }
        }

        if ($tagUpdateStruct->remoteId !== null && (!is_string($tagUpdateStruct->remoteId) || $tagUpdateStruct->remoteId === '')) {
            throw new InvalidArgumentValue('remoteId', $tagUpdateStruct->remoteId, 'TagUpdateStruct');
        }

        $spiTag = $this->tagsHandler->load($tag->id);

        if ($tagUpdateStruct->remoteId !== null) {
            try {
                $existingTag = $this->tagsHandler->loadTagInfoByRemoteId($tagUpdateStruct->remoteId);
                if ($existingTag->id !== $spiTag->id) {
                    throw new InvalidArgumentException('tagUpdateStruct', 'Tag with provided remote ID already exists');
                }
            } catch (NotFoundException $e) {
                // Do nothing
            }
        }

        if ($tagUpdateStruct->mainLanguageCode !== null && (!is_string($tagUpdateStruct->mainLanguageCode) || $tagUpdateStruct->mainLanguageCode === '')) {
            throw new InvalidArgumentValue('mainLanguageCode', $tagUpdateStruct->mainLanguageCode, 'TagUpdateStruct');
        }

        $mainLanguageCode = $spiTag->mainLanguageCode;
        if ($tagUpdateStruct->mainLanguageCode !== null) {
            $mainLanguageCode = $tagUpdateStruct->mainLanguageCode;
        }

        $newKeywords = $spiTag->keywords;
        if ($keywords !== null) {
            $newKeywords = $keywords;
        }

        if (!isset($newKeywords[$mainLanguageCode])) {
            throw new InvalidArgumentValue('mainLanguageCode', $tagUpdateStruct->mainLanguageCode, 'TagUpdateStruct');
        }

        if ($tagUpdateStruct->alwaysAvailable !== null && !is_bool($tagUpdateStruct->alwaysAvailable)) {
            throw new InvalidArgumentValue('alwaysAvailable', $tagUpdateStruct->alwaysAvailable, 'TagUpdateStruct');
        }

        $updateStruct = new UpdateStruct();
        $updateStruct->keywords = $newKeywords ?? $spiTag->keywords;
        $updateStruct->remoteId = $tagUpdateStruct->remoteId !== null ? trim($tagUpdateStruct->remoteId) : $spiTag->remoteId;
        $updateStruct->mainLanguageCode = $tagUpdateStruct->mainLanguageCode !== null ? trim($tagUpdateStruct->mainLanguageCode) : $spiTag->mainLanguageCode;
        $updateStruct->alwaysAvailable = $tagUpdateStruct->alwaysAvailable ?? $spiTag->alwaysAvailable;

        $this->repository->beginTransaction();

        try {
            $updatedTag = $this->tagsHandler->update($updateStruct, $spiTag->id);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();

            throw $e;
        }

        return $this->buildTagDomainObject($updatedTag);
    }

    public function addSynonym(SynonymCreateStruct $synonymCreateStruct): Tag
    {
        $keywords = $synonymCreateStruct->getKeywords();

        if ($this->hasAccess('tags', 'addsynonym') === false) {
            throw new UnauthorizedException('tags', 'addsynonym');
        }

        $mainTag = $this->tagsHandler->loadTagInfo($synonymCreateStruct->mainTagId);
        if ($mainTag->mainTagId > 0) {
            throw new InvalidArgumentValue('mainTagId', $synonymCreateStruct->mainTagId, 'SynonymCreateStruct');
        }

        if (!is_string($synonymCreateStruct->mainLanguageCode) || $synonymCreateStruct->mainLanguageCode === '') {
            throw new InvalidArgumentValue('mainLanguageCode', $synonymCreateStruct->mainLanguageCode, 'SynonymCreateStruct');
        }

        if (!is_array($keywords) || count($keywords) === 0) {
            throw new InvalidArgumentValue('keywords', $keywords, 'SynonymCreateStruct');
        }

        if (!isset($keywords[$synonymCreateStruct->mainLanguageCode])) {
            throw new InvalidArgumentValue('keywords', $keywords, 'SynonymCreateStruct');
        }

        if ($synonymCreateStruct->remoteId !== null && (!is_string($synonymCreateStruct->remoteId) || $synonymCreateStruct->remoteId === '')) {
            throw new InvalidArgumentValue('remoteId', $synonymCreateStruct->remoteId, 'SynonymCreateStruct');
        }

        // check for existence of tag with provided remote ID
        if ($synonymCreateStruct->remoteId !== null) {
            try {
                $this->tagsHandler->loadTagInfoByRemoteId($synonymCreateStruct->remoteId);

                throw new InvalidArgumentException('synonymCreateStruct', 'Tag with provided remote ID already exists');
            } catch (NotFoundException $e) {
                // Do nothing
            }
        } else {
            $synonymCreateStruct->remoteId = md5(uniqid(get_class($this), true));
        }

        if (!is_bool($synonymCreateStruct->alwaysAvailable)) {
            throw new InvalidArgumentValue('alwaysAvailable', $synonymCreateStruct->alwaysAvailable, 'SynonymCreateStruct');
        }

        $createStruct = new SPISynonymCreateStruct();
        $createStruct->mainTagId = $synonymCreateStruct->mainTagId;
        $createStruct->mainLanguageCode = $synonymCreateStruct->mainLanguageCode;
        $createStruct->keywords = $keywords;
        $createStruct->remoteId = $synonymCreateStruct->remoteId;
        $createStruct->alwaysAvailable = $synonymCreateStruct->alwaysAvailable;

        $this->repository->beginTransaction();

        try {
            $newTag = $this->tagsHandler->addSynonym($createStruct);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();

            throw $e;
        }

        return $this->buildTagDomainObject($newTag);
    }

    public function convertToSynonym(Tag $tag, Tag $mainTag): Tag
    {
        if ($this->hasAccess('tags', 'makesynonym') === false) {
            throw new UnauthorizedException('tags', 'makesynonym');
        }

        $spiTagInfo = $this->tagsHandler->loadTagInfo($tag->id);
        $spiMainTagInfo = $this->tagsHandler->loadTagInfo($mainTag->id);

        if ($spiTagInfo->mainTagId > 0) {
            throw new InvalidArgumentException('tag', 'Source tag is a synonym');
        }

        if ($spiMainTagInfo->mainTagId > 0) {
            throw new InvalidArgumentException('mainTag', 'Destination tag is a synonym');
        }

        if (mb_strpos($spiMainTagInfo->pathString, $spiTagInfo->pathString) === 0) {
            throw new InvalidArgumentException('mainTag', 'Destination tag is a sub tag of the given tag');
        }

        $this->repository->beginTransaction();

        try {
            foreach ($this->tagsHandler->loadChildren($spiTagInfo->id) as $child) {
                $this->tagsHandler->moveSubtree($child->id, $spiMainTagInfo->id);
            }

            $convertedTag = $this->tagsHandler->convertToSynonym($spiTagInfo->id, $spiMainTagInfo->id);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();

            throw $e;
        }

        return $this->buildTagDomainObject($convertedTag);
    }

    public function mergeTags(Tag $tag, Tag $targetTag): void
    {
        if ($this->hasAccess('tags', 'merge') === false) {
            throw new UnauthorizedException('tags', 'merge');
        }

        $spiTagInfo = $this->tagsHandler->loadTagInfo($tag->id);
        $spiTargetTagInfo = $this->tagsHandler->loadTagInfo($targetTag->id);

        if ($spiTagInfo->mainTagId > 0) {
            throw new InvalidArgumentException('tag', 'Source tag is a synonym');
        }

        if ($spiTargetTagInfo->mainTagId > 0) {
            throw new InvalidArgumentException('targetTag', 'Target tag is a synonym');
        }

        if (mb_strpos($spiTargetTagInfo->pathString, $spiTagInfo->pathString) === 0) {
            throw new InvalidArgumentException('targetParentTag', 'Target tag is a sub tag of the given tag');
        }

        $this->repository->beginTransaction();

        try {
            foreach ($this->tagsHandler->loadChildren($spiTagInfo->id) as $child) {
                $this->tagsHandler->moveSubtree($child->id, $spiTargetTagInfo->id);
            }

            $this->tagsHandler->merge($spiTagInfo->id, $spiTargetTagInfo->id);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();

            throw $e;
        }
    }

    public function copySubtree(Tag $tag, ?Tag $targetParentTag = null): Tag
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        $spiTagInfo = $this->tagsHandler->loadTagInfo($tag->id);

        if ($spiTagInfo->mainTagId > 0) {
            throw new InvalidArgumentException('tag', 'Source tag is a synonym');
        }

        if (!$targetParentTag instanceof Tag && $tag->parentTagId === 0) {
            throw new InvalidArgumentException('targetParentTag', 'Tag is already located at the root of the tree');
        }

        if ($targetParentTag instanceof Tag && $tag->parentTagId === $targetParentTag->id) {
            throw new InvalidArgumentException('targetParentTag', 'Target parent tag is already the parent of the given tag');
        }

        $spiParentTagInfo = null;
        if ($targetParentTag instanceof Tag) {
            $spiParentTagInfo = $this->tagsHandler->loadTagInfo($targetParentTag->id);

            if ($spiParentTagInfo->mainTagId > 0) {
                throw new InvalidArgumentException('targetParentTag', 'Target parent tag is a synonym');
            }

            if (mb_strpos($spiParentTagInfo->pathString, $spiTagInfo->pathString) === 0) {
                throw new InvalidArgumentException('targetParentTag', 'Target parent tag is a sub tag of the given tag');
            }
        }

        $this->repository->beginTransaction();

        try {
            $copiedTag = $this->tagsHandler->copySubtree(
                $spiTagInfo->id,
                $spiParentTagInfo ? $spiParentTagInfo->id : 0
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();

            throw $e;
        }

        return $this->buildTagDomainObject($copiedTag);
    }

    public function moveSubtree(Tag $tag, ?Tag $targetParentTag = null): Tag
    {
        if ($this->hasAccess('tags', 'edit') === false) {
            throw new UnauthorizedException('tags', 'edit');
        }

        $spiTagInfo = $this->tagsHandler->loadTagInfo($tag->id);

        if ($spiTagInfo->mainTagId > 0) {
            throw new InvalidArgumentException('tag', 'Source tag is a synonym');
        }

        if (!$targetParentTag instanceof Tag && $tag->parentTagId === 0) {
            throw new InvalidArgumentException('targetParentTag', 'Tag is already located at the root of the tree');
        }

        if ($targetParentTag instanceof Tag && $tag->parentTagId === $targetParentTag->id) {
            throw new InvalidArgumentException('targetParentTag', 'Target parent tag is already the parent of the given tag');
        }

        $spiParentTagInfo = null;
        if ($targetParentTag instanceof Tag) {
            $spiParentTagInfo = $this->tagsHandler->loadTagInfo($targetParentTag->id);

            if ($spiParentTagInfo->mainTagId > 0) {
                throw new InvalidArgumentException('targetParentTag', 'Target parent tag is a synonym');
            }

            if (mb_strpos($spiParentTagInfo->pathString, $spiTagInfo->pathString) === 0) {
                throw new InvalidArgumentException('targetParentTag', 'Target parent tag is a sub tag of the given tag');
            }
        }

        $this->repository->beginTransaction();

        try {
            $movedTag = $this->tagsHandler->moveSubtree(
                $spiTagInfo->id,
                $spiParentTagInfo ? $spiParentTagInfo->id : 0
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();

            throw $e;
        }

        return $this->buildTagDomainObject($movedTag);
    }

    public function deleteTag(Tag $tag): void
    {
        if ($tag->mainTagId > 0) {
            if ($this->hasAccess('tags', 'deletesynonym') === false) {
                throw new UnauthorizedException('tags', 'deletesynonym');
            }
        } elseif ($this->hasAccess('tags', 'delete') === false) {
            throw new UnauthorizedException('tags', 'delete');
        }

        $this->repository->beginTransaction();

        try {
            $this->tagsHandler->deleteTag($tag->id);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();

            throw $e;
        }
    }

    public function newTagCreateStruct(int $parentTagId, string $mainLanguageCode): TagCreateStruct
    {
        $tagCreateStruct = new TagCreateStruct();
        $tagCreateStruct->parentTagId = $parentTagId;
        $tagCreateStruct->mainLanguageCode = $mainLanguageCode;

        return $tagCreateStruct;
    }

    public function newSynonymCreateStruct(int $mainTagId, string $mainLanguageCode): SynonymCreateStruct
    {
        $synonymCreateStruct = new SynonymCreateStruct();
        $synonymCreateStruct->mainTagId = $mainTagId;
        $synonymCreateStruct->mainLanguageCode = $mainLanguageCode;

        return $synonymCreateStruct;
    }

    public function newTagUpdateStruct(): TagUpdateStruct
    {
        return new TagUpdateStruct();
    }

    public function sudo(callable $callback, ?TagsServiceInterface $outerTagsService = null)
    {
        ++$this->sudoNestingLevel;

        try {
            $returnValue = $callback($outerTagsService ?? $this);
        } catch (Exception $e) {
            --$this->sudoNestingLevel;

            throw $e;
        }

        --$this->sudoNestingLevel;

        return $returnValue;
    }

    /**
     * Low level permission function: Returns boolean value, or an array of limitations that user permission depends on.
     *
     * Note: boolean value describes full access (true) or no access at all (false), array can be seen as a maybe..
     *
     * @param string $module The module, aka controller identifier to check permissions on
     * @param string $function The function, aka the controller action to check permissions on
     * @param \eZ\Publish\API\Repository\Values\User\UserReference|null $userReference User for
     *        which the information is returned, current user will be used if null
     *
     * @return bool|array if limitations are on this function an array of limitations is returned
     */
    public function hasAccess(string $module, string $function, ?UserReference $userReference = null)
    {
        // Full access if sudo nesting level is set by sudo method
        if ($this->sudoNestingLevel > 0) {
            return true;
        }

        return $this->repository->getPermissionResolver()->hasAccess($module, $function, $userReference);
    }

    /**
     * Indicates if the current user is allowed to perform an action given by the function on the given
     * objects.
     *
     * @param string $module The module, aka controller identifier to check permissions on
     * @param string $function The function, aka the controller action to check permissions on
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object The object to check if the user has access to
     * @param \eZ\Publish\API\Repository\Values\ValueObject[] $targets An array of location, parent or "assignment" value objects
     *
     * @return bool
     */
    public function canUser(string $module, string $function, ValueObject $object, array $targets = []): bool
    {
        $permissionSets = $this->hasAccess($module, $function);
        if ($permissionSets === false || $permissionSets === true) {
            return $permissionSets;
        }

        return $this->repository->getPermissionResolver()->canUser($module, $function, $object, $targets);
    }

    private function buildTagDomainObject(SPITag $spiTag): Tag
    {
        return $this->buildTagDomainList([$spiTag])[$spiTag->id];
    }

    private function buildTagDomainList(array $spiTags): array
    {
        $languageIds = [[]];
        foreach ($spiTags as $spiTag) {
            $languageIds[] = $spiTag->languageIds;
        }

        $languages = $this->languageHandler->loadList(array_unique(array_merge(...$languageIds)));

        $tags = [];
        foreach ($spiTags as $spiTag) {
            $languageCodes = [];
            foreach ($spiTag->languageIds as $languageId) {
                if (isset($languages[$languageId])) {
                    $languageCodes[] = $languages[$languageId]->languageCode;
                }
            }

            $tags[$spiTag->id] = new Tag(
                [
                    'id' => $spiTag->id,
                    'parentTagId' => $spiTag->parentTagId,
                    'mainTagId' => $spiTag->mainTagId,
                    'keywords' => $spiTag->keywords,
                    'depth' => $spiTag->depth,
                    'pathString' => $spiTag->pathString,
                    'modificationDate' => new DateTimeImmutable('@' . $spiTag->modificationDate),
                    'remoteId' => $spiTag->remoteId,
                    'alwaysAvailable' => $spiTag->alwaysAvailable,
                    'mainLanguageCode' => $spiTag->mainLanguageCode,
                    'languageCodes' => $languageCodes,
                ]
            );
        }

        return $tags;
    }
}
