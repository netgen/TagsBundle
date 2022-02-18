<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository;

use Netgen\TagsBundle\API\Repository\Values\Tags\SearchResult;
use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagList;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;

interface TagsService
{
    /**
     * Loads a tag object from its $tagId.
     *
     * @param int $tagId
     * @param array|null $languages A language filter for keywords. If not given all languages are returned
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTag(int $tagId, ?array $languages = null, bool $useAlwaysAvailable = true): Tag;

    /**
     * Loads a tag object from array of $tagIds.
     *
     * Tags missing (NotFound), or not accessible (Unauthorized) to the current user will be filtered out from the
     * returned array. As returned array has tag id's as keys, you can use array_keys + array_diff to get missing items
     *
     * @param array $tagIds
     * @param array|null $languages A language filter for keywords. If not given all languages are returned
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\TagList Key of array is the corresponding tag id
     */
    public function loadTagList(array $tagIds, ?array $languages = null, bool $useAlwaysAvailable = true): TagList;

    /**
     * Loads a tag object from its $remoteId.
     *
     * @param string $remoteId
     * @param array|null $languages A language filter for keywords. If not given all languages are returned
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function loadTagByRemoteId(string $remoteId, ?array $languages = null, bool $useAlwaysAvailable = true): Tag;

    /**
     * Loads a tag object from its URL.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function loadTagByUrl(string $url, array $languages): Tag;

    /**
     * Loads children of a tag object.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag If null, tags from the first level will be returned
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all children starting at $offset are returned
     * @param array|null $languages A language filter for keywords. If not given all languages are returned
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\TagList
     */
    public function loadTagChildren(?Tag $tag = null, int $offset = 0, int $limit = -1, ?array $languages = null, bool $useAlwaysAvailable = true): TagList;

    /**
     * Returns the number of children of a tag object.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag If null, tag count from the first level will be returned
     * @param array|null $languages A language filter for keywords. If not given all languages are returned
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @return int
     */
    public function getTagChildrenCount(?Tag $tag = null, ?array $languages = null, bool $useAlwaysAvailable = true): int;

    /**
     * Loads tags by specified keyword.
     *
     * @param string $keyword The keyword to fetch tags for
     * @param string $language The language to check for
     * @param bool $useAlwaysAvailable Check for main language if true (default) and if tag is always available
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all children starting at $offset are returned
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\TagList
     */
    public function loadTagsByKeyword(string $keyword, string $language, bool $useAlwaysAvailable = true, int $offset = 0, int $limit = -1): TagList;

    /**
     * Returns the number of tags by specified keyword.
     *
     * @param string $keyword The keyword to fetch tags count for
     * @param string $language The language to check for
     * @param bool $useAlwaysAvailable Check for main language if true (default) and if tag is always available
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @return int
     */
    public function getTagsByKeywordCount(string $keyword, string $language, bool $useAlwaysAvailable = true): int;

    /**
     * Search for tags.
     *
     * @param string $searchString Search string
     * @param string $language The language to search for
     * @param bool $useAlwaysAvailable Check for main language if true (default) and if tag is always available
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all found tags starting at $offset are returned
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\SearchResult
     */
    public function searchTags(string $searchString, string $language, bool $useAlwaysAvailable = true, int $offset = 0, int $limit = -1): SearchResult;

    /**
     * Loads synonyms of a tag object.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param int $offset The start offset for paging
     * @param int $limit The number of synonyms returned. If $limit = -1 all synonyms starting at $offset are returned
     * @param array|null $languages A language filter for keywords. If not given all languages are returned
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If the tag is already a synonym
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\TagList
     */
    public function loadTagSynonyms(Tag $tag, int $offset = 0, int $limit = -1, ?array $languages = null, bool $useAlwaysAvailable = true): TagList;

    /**
     * Returns the number of synonyms of a tag object.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param array|null $languages A language filter for keywords. If not given all languages are returned
     * @param bool $useAlwaysAvailable Add main language to $languages if true (default) and if tag is always available
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If the tag is already a synonym
     *
     * @return int
     */
    public function getTagSynonymCount(Tag $tag, ?array $languages = null, bool $useAlwaysAvailable = true): int;

    /**
     * Loads content related to $tag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param int $offset The start offset for paging
     * @param int $limit The number of content objects returned. If $limit = -1 all content objects starting at $offset are returned
     * @param bool $returnContentInfo
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion[] $additionalCriteria Additional criteria for filtering related content
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause[] $sortClauses
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Content[]|\Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo[]
     */
    public function getRelatedContent(Tag $tag, int $offset = 0, int $limit = -1, bool $returnContentInfo = true, array $additionalCriteria = [], array $sortClauses = []): array;

    /**
     * Returns the number of content objects related to $tag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion[] $additionalCriteria Additional criteria for filtering related content
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @return int
     */
    public function getRelatedContentCount(Tag $tag, array $additionalCriteria = []): int;

    /**
     * Creates the new tag.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to create this tag
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If the remote ID already exists
     */
    public function createTag(TagCreateStruct $tagCreateStruct): Tag;

    /**
     * Updates $tag.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to update this tag
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If the remote ID already exists
     */
    public function updateTag(Tag $tag, TagUpdateStruct $tagUpdateStruct): Tag;

    /**
     * Creates a synonym for $tag.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to create a synonym
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If the target tag is a synonym
     */
    public function addSynonym(SynonymCreateStruct $synonymCreateStruct): Tag;

    /**
     * Converts $tag to a synonym of $mainTag.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to convert tag to synonym
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If either one of the tags is a synonym
     *                                                                        If the main tag is a sub tag of the given tag
     */
    public function convertToSynonym(Tag $tag, Tag $mainTag): Tag;

    /**
     * Merges the $tag into the $targetTag.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to merge tags
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If either one of the tags is a synonym
     *                                                                        If the target tag is a sub tag of the given tag
     */
    public function mergeTags(Tag $tag, Tag $targetTag): void;

    /**
     * Copies the subtree starting from $tag as a new subtree of $targetParentTag.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to read tags
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If the target tag is a sub tag of the given tag
     *                                                                        If the target tag is already a parent of the given tag
     *                                                                        If either one of the tags is a synonym
     */
    public function copySubtree(Tag $tag, ?Tag $targetParentTag = null): Tag;

    /**
     * Moves the subtree to $targetParentTag.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If either of specified tags is not found
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to move this tag
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If the target tag is a sub tag of the given tag
     *                                                                        If the target tag is already a parent of the given tag
     *                                                                        If either one of the tags is a synonym
     */
    public function moveSubtree(Tag $tag, ?Tag $targetParentTag = null): Tag;

    /**
     * Deletes $tag and all its descendants and synonyms.
     *
     * If $tag is a synonym, only the synonym is deleted
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException If the current user is not allowed to delete this tag
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException If the specified tag is not found
     */
    public function deleteTag(Tag $tag): void;

    /**
     * Instantiates a new tag create struct.
     */
    public function newTagCreateStruct(int $parentTagId, string $mainLanguageCode): TagCreateStruct;

    /**
     * Instantiates a new synonym create struct.
     */
    public function newSynonymCreateStruct(int $mainTagId, string $mainLanguageCode): SynonymCreateStruct;

    /**
     * Instantiates a new tag update struct.
     */
    public function newTagUpdateStruct(): TagUpdateStruct;

    /**
     * Allows tags API execution to be performed with full access sand-boxed.
     *
     * The closure sandbox will do a catch all on exceptions and rethrow after
     * re-setting the sudo flag.
     *
     * Example use:
     *     $tag = $tagsService->sudo(
     *         static function (TagsService $tagsService) use ($tagId): Tag {
     *             return $tagsService->loadTag($tagId);
     *         }
     *     );
     *
     * @throws \RuntimeException Thrown on recursive sudo() use
     * @throws \Exception Re throws exceptions thrown inside $callback
     *
     * @return mixed
     */
    public function sudo(callable $callback, ?TagsService $outerTagsService = null);
}
