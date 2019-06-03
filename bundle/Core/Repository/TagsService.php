<?php

namespace Netgen\TagsBundle\Core\Repository;

use Closure;
use DateTime;
use Exception;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\User\User;
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

class TagsService implements TagsServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \Netgen\TagsBundle\SPI\Persistence\Tags\Handler
     */
    protected $tagsHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Counter for the current sudo nesting level.
     *
     * @var int
     */
    protected $sudoNestingLevel = 0;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\Handler $tagsHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     */
    public function __construct(
        Repository $repository,
        TagsHandler $tagsHandler,
        LanguageHandler $languageHandler
    ) {
        $this->repository = $repository;
        $this->tagsHandler = $tagsHandler;
        $this->languageHandler = $languageHandler;
    }

    /**
     * Loads a tag object from its $tagId.
     *
     * @param mixed $tagId
     * @param array|null $languages A language filter for keywords. If not given all languages are returned
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTag($tagId, array $languages = null, $useAlwaysAvailable = true)
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

    /**
     * {@inheritdoc}
     */
    public function loadTagList(array $tagIds, array $languages = null, $useAlwaysAvailable = true)
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

    /**
     * Loads a tag object from its $remoteId.
     *
     * @param string $remoteId
     * @param array|null $languages A language filter for keywords. If not given all languages are returned
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTagByRemoteId($remoteId, array $languages = null, $useAlwaysAvailable = true)
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

    /**
     * Loads a tag object from its URL.
     *
     * @param string $url
     * @param string[] $languages
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTagByUrl($url, array $languages)
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        $keywordArray = explode('/', trim($url, '/'));
        if (!is_array($keywordArray) || empty($keywordArray)) {
            throw new InvalidArgumentValue('url', $url);
        }

        $parentId = 0;
        $spiTag = null;

        if (!empty($languages)) {
            foreach ($keywordArray as $keyword) {
                if (empty($keyword)) {
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
                if (!empty($spiTagKeywords) && $spiTagKeywords[0] !== $keyword) {
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

    /**
     * Loads children of a tag object.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag If null, tags from the first level will be returned
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all children starting at $offset are returned
     * @param array|null $languages A language filter for keywords. If not given all languages are returned
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public function loadTagChildren(Tag $tag = null, $offset = 0, $limit = -1, array $languages = null, $useAlwaysAvailable = true)
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

    /**
     * Returns the number of children of a tag object.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag If null, tag count from the first level will be returned
     * @param array|null $languages A language filter for keywords. If not given all languages are returned
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @return int
     */
    public function getTagChildrenCount(Tag $tag = null, array $languages = null, $useAlwaysAvailable = true)
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

    /**
     * Loads tags by specified keyword.
     *
     * @param string $keyword The keyword to fetch tags for
     * @param string $language The language to check for
     * @param bool $useAlwaysAvailable Check for main language if true (default) and if tag is always available
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all children starting at $offset are returned
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public function loadTagsByKeyword($keyword, $language, $useAlwaysAvailable = true, $offset = 0, $limit = -1)
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

    /**
     * Returns the number of tags by specified keyword.
     *
     * @param string $keyword The keyword to fetch tags count for
     * @param string $language The language to check for
     * @param bool $useAlwaysAvailable Check for main language if true (default) and if tag is always available
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @return int
     */
    public function getTagsByKeywordCount($keyword, $language, $useAlwaysAvailable = true)
    {
        if ($this->hasAccess('tags', 'read') === false) {
            throw new UnauthorizedException('tags', 'read');
        }

        return $this->tagsHandler->getTagsByKeywordCount($keyword, $language, $useAlwaysAvailable);
    }

    /**
     * Search for tags.
     *
     * @param string $searchString Search string
     * @param string $language The language to search for
     * @param bool $useAlwaysAvailable Check for main language if true (default) and if tag is always available
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all found tags starting at $offset are returned
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\SearchResult
     */
    public function searchTags($searchString, $language, $useAlwaysAvailable = true, $offset = 0, $limit = -1)
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

    /**
     * Loads synonyms of a tag object.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param int $offset The start offset for paging
     * @param int $limit The number of synonyms returned. If $limit = -1 all synonyms starting at $offset are returned
     * @param array|null $languages A language filter for keywords. If not given all languages are returned
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the tag is already a synonym
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    public function loadTagSynonyms(Tag $tag, $offset = 0, $limit = -1, array $languages = null, $useAlwaysAvailable = true)
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

    /**
     * Returns the number of synonyms of a tag object.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param array|null $languages A language filter for keywords. If not given all languages are returned
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the tag is already a synonym
     *
     * @return int
     */
    public function getTagSynonymCount(Tag $tag, array $languages = null, $useAlwaysAvailable = true)
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

    /**
     * Loads content related to $tag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param int $offset The start offset for paging
     * @param int $limit The number of content objects returned. If $limit = -1 all content objects starting at $offset are returned
     * @param bool $returnContentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion[] $additionalCriteria Additional criteria for filtering related content
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]|\eZ\Publish\API\Repository\Values\Content\ContentInfo[]
     */
    public function getRelatedContent(Tag $tag, $offset = 0, $limit = -1, $returnContentInfo = true, array $additionalCriteria = [], array $sortClauses = [])
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

        if (empty($sortClauses)) {
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

    /**
     * Returns the number of content objects related to $tag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion[] $additionalCriteria Additional criteria for filtering related content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return int
     */
    public function getRelatedContentCount(Tag $tag, array $additionalCriteria = [])
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

    /**
     * Creates the new tag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct $tagCreateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to create this tag
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the remote ID already exists
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag The newly created tag
     */
    public function createTag(TagCreateStruct $tagCreateStruct)
    {
        $keywords = $tagCreateStruct->getKeywords();

        if (!empty($tagCreateStruct->parentTagId)) {
            if ($this->canUser('tags', 'add', $this->loadTag($tagCreateStruct->parentTagId)) !== true) {
                throw new UnauthorizedException('tags', 'add');
            }
        } elseif ($this->hasAccess('tags', 'add') === false) {
            throw new UnauthorizedException('tags', 'add');
        }

        if (empty($tagCreateStruct->mainLanguageCode) || !is_string($tagCreateStruct->mainLanguageCode)) {
            throw new InvalidArgumentValue('mainLanguageCode', $tagCreateStruct->mainLanguageCode, 'TagCreateStruct');
        }

        if (empty($keywords) || !is_array($keywords)) {
            throw new InvalidArgumentValue('keywords', $keywords, 'TagCreateStruct');
        }

        if (!isset($keywords[$tagCreateStruct->mainLanguageCode])) {
            throw new InvalidArgumentValue('keywords', $keywords, 'TagCreateStruct');
        }

        if ($tagCreateStruct->remoteId !== null && (empty($tagCreateStruct->remoteId) || !is_string($tagCreateStruct->remoteId))) {
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
        $createStruct->parentTagId = !empty($tagCreateStruct->parentTagId) ? $tagCreateStruct->parentTagId : 0;
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

    /**
     * Updates $tag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct $tagUpdateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to update this tag
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the remote ID already exists
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag The updated tag
     */
    public function updateTag(Tag $tag, TagUpdateStruct $tagUpdateStruct)
    {
        $keywords = $tagUpdateStruct->getKeywords();

        if ($tag->mainTagId > 0) {
            if ($this->hasAccess('tags', 'edit') === false) {
                throw new UnauthorizedException('tags', 'edit');
            }
        } else {
            if ($this->hasAccess('tags', 'editsynonym') === false) {
                throw new UnauthorizedException('tags', 'editsynonym');
            }
        }

        if ($keywords !== null && (!is_array($keywords) || empty($keywords))) {
            throw new InvalidArgumentValue('keywords', $keywords, 'TagUpdateStruct');
        }

        if ($keywords !== null) {
            foreach ($keywords as $keyword) {
                if (empty($keyword)) {
                    throw new InvalidArgumentValue('keywords', $keywords, 'TagUpdateStruct');
                }
            }
        }

        if ($tagUpdateStruct->remoteId !== null && (!is_string($tagUpdateStruct->remoteId) || empty($tagUpdateStruct->remoteId))) {
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

        if ($tagUpdateStruct->mainLanguageCode !== null && (!is_string($tagUpdateStruct->mainLanguageCode) || empty($tagUpdateStruct->mainLanguageCode))) {
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
        $updateStruct->keywords = $newKeywords !== null ? $newKeywords : $spiTag->keywords;
        $updateStruct->remoteId = $tagUpdateStruct->remoteId !== null ? trim($tagUpdateStruct->remoteId) : $spiTag->remoteId;
        $updateStruct->mainLanguageCode = $tagUpdateStruct->mainLanguageCode !== null ? trim($tagUpdateStruct->mainLanguageCode) : $spiTag->mainLanguageCode;
        $updateStruct->alwaysAvailable = $tagUpdateStruct->alwaysAvailable !== null ? $tagUpdateStruct->alwaysAvailable : $spiTag->alwaysAvailable;

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

    /**
     * Creates a synonym for $tag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct $synonymCreateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to create a synonym
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target tag is a synonym
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag The created synonym
     */
    public function addSynonym(SynonymCreateStruct $synonymCreateStruct)
    {
        $keywords = $synonymCreateStruct->getKeywords();

        if ($this->hasAccess('tags', 'addsynonym') === false) {
            throw new UnauthorizedException('tags', 'addsynonym');
        }

        $mainTag = $this->tagsHandler->loadTagInfo($synonymCreateStruct->mainTagId);
        if ($mainTag->mainTagId > 0) {
            throw new InvalidArgumentValue('mainTagId', $synonymCreateStruct->mainTagId, 'SynonymCreateStruct');
        }

        if (empty($synonymCreateStruct->mainLanguageCode) || !is_string($synonymCreateStruct->mainLanguageCode)) {
            throw new InvalidArgumentValue('mainLanguageCode', $synonymCreateStruct->mainLanguageCode, 'SynonymCreateStruct');
        }

        if (empty($keywords) || !is_array($keywords)) {
            throw new InvalidArgumentValue('keywords', $keywords, 'SynonymCreateStruct');
        }

        if (!isset($keywords[$synonymCreateStruct->mainLanguageCode])) {
            throw new InvalidArgumentValue('keywords', $keywords, 'SynonymCreateStruct');
        }

        if ($synonymCreateStruct->remoteId !== null && (empty($synonymCreateStruct->remoteId) || !is_string($synonymCreateStruct->remoteId))) {
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

    /**
     * Converts $tag to a synonym of $mainTag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $mainTag
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to convert tag to synonym
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If either one of the tags is a synonym
     *                                                                        If the main tag is a sub tag of the given tag
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag The converted synonym
     */
    public function convertToSynonym(Tag $tag, Tag $mainTag)
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

    /**
     * Merges the $tag into the $targetTag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $targetTag
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to merge tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If either one of the tags is a synonym
     *                                                                        If the target tag is a sub tag of the given tag
     */
    public function mergeTags(Tag $tag, Tag $targetTag)
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

    /**
     * Copies the subtree starting from $tag as a new subtree of $targetParentTag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag The subtree denoted by the tag to copy
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $targetParentTag The target parent tag for the copy operation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target tag is a sub tag of the given tag
     *                                                                        If the target tag is already a parent of the given tag
     *                                                                        If either one of the tags is a synonym
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag The newly created tag of the copied subtree
     */
    public function copySubtree(Tag $tag, Tag $targetParentTag = null)
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

    /**
     * Moves the subtree to $targetParentTag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $targetParentTag
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to move this tag
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target tag is a sub tag of the given tag
     *                                                                        If the target tag is already a parent of the given tag
     *                                                                        If either one of the tags is a synonym
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag The updated root tag of the moved subtree
     */
    public function moveSubtree(Tag $tag, Tag $targetParentTag = null)
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

    /**
     * Deletes $tag and all its descendants and synonyms.
     *
     * If $tag is a synonym, only the synonym is deleted
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to delete this tag
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function deleteTag(Tag $tag)
    {
        if ($tag->mainTagId > 0) {
            if ($this->hasAccess('tags', 'deletesynonym') === false) {
                throw new UnauthorizedException('tags', 'deletesynonym');
            }
        } else {
            if ($this->hasAccess('tags', 'delete') === false) {
                throw new UnauthorizedException('tags', 'delete');
            }
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

    /**
     * Instantiates a new tag create struct.
     *
     * @param mixed $parentTagId
     * @param string $mainLanguageCode
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct
     */
    public function newTagCreateStruct($parentTagId, $mainLanguageCode)
    {
        $tagCreateStruct = new TagCreateStruct();
        $tagCreateStruct->parentTagId = $parentTagId;
        $tagCreateStruct->mainLanguageCode = $mainLanguageCode;

        return $tagCreateStruct;
    }

    /**
     * Instantiates a new synonym create struct.
     *
     * @param mixed $mainTagId
     * @param string $mainLanguageCode
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct
     */
    public function newSynonymCreateStruct($mainTagId, $mainLanguageCode)
    {
        $synonymCreateStruct = new SynonymCreateStruct();
        $synonymCreateStruct->mainTagId = $mainTagId;
        $synonymCreateStruct->mainLanguageCode = $mainLanguageCode;

        return $synonymCreateStruct;
    }

    /**
     * Instantiates a new tag update struct.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct
     */
    public function newTagUpdateStruct()
    {
        return new TagUpdateStruct();
    }

    public function sudo(Closure $callback, TagsServiceInterface $outerTagsService = null)
    {
        ++$this->sudoNestingLevel;

        try {
            $returnValue = $callback($outerTagsService !== null ? $outerTagsService : $this);
        } catch (Exception $e) {
            --$this->sudoNestingLevel;

            throw $e;
        }

        --$this->sudoNestingLevel;

        return $returnValue;
    }

    /**
     * Checks if user has access to specified module and function.
     *
     * @param string $module The module, aka controller identifier to check permissions on
     * @param string $function The function, aka the controller action to check permissions on
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return bool|array if limitations are on this function an array of limitations is returned
     */
    public function hasAccess($module, $function, User $user = null)
    {
        // Full access if sudo nesting level is set by sudo method
        if ($this->sudoNestingLevel > 0) {
            return true;
        }

        return $this->repository->hasAccess($module, $function, $user);
    }

    /**
     * Indicates if the current user is allowed to perform an action given by the function on the given
     * objects.
     *
     * @param string $module The module, aka controller identifier to check permissions on
     * @param string $function The function, aka the controller action to check permissions on
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object The object to check if the user has access to
     * @param mixed $targets The location, parent or "assignment" value object, or an array of the same
     *
     * @return bool
     */
    public function canUser($module, $function, ValueObject $object, $targets = null)
    {
        $permissionSets = $this->hasAccess($module, $function);
        if ($permissionSets === false || $permissionSets === true) {
            return $permissionSets;
        }

        return $this->repository->canUser($module, $function, $object, $targets);
    }

    protected function buildTagDomainObject(SPITag $spiTag)
    {
        return $this->buildTagDomainList([$spiTag])[$spiTag->id];
    }

    protected function buildTagDomainList(array $spiTags)
    {
        // Optimization for 2.5+ to load all languages at once:
        if (\method_exists($this->languageHandler, 'loadList')) {
            $languageIds = [[]];
            foreach ($spiTags as $spiTag) {
                $languageIds[] = $spiTag->languageIds;
            }

            $languages = $this->languageHandler->loadList(\array_unique(\array_merge(...$languageIds)));
        }

        $tags = [];
        foreach ($spiTags as $spiTag) {
            $languageCodes = [];
            foreach ($spiTag->languageIds as $languageId) {
                if (isset($languages[$languageId])) {
                    // 2.5+
                    $languageCodes[] = $languages[$languageId]->languageCode;
                } elseif (!isset($languages)) {
                    // @deprecated Compat code for eZ Platform 1.x
                    $languageCodes[] = $this->languageHandler->load($languageId)->languageCode;
                }
            }

            $modificationDate = new DateTime();
            $modificationDate->setTimestamp($spiTag->modificationDate);

            $tags[$spiTag->id] = new Tag(
                [
                    'id' => $spiTag->id,
                    'parentTagId' => $spiTag->parentTagId,
                    'mainTagId' => $spiTag->mainTagId,
                    'keywords' => $spiTag->keywords,
                    'depth' => $spiTag->depth,
                    'pathString' => $spiTag->pathString,
                    'modificationDate' => $modificationDate,
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
