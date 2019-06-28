<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\SignalSlot;

use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use Netgen\TagsBundle\API\Repository\TagsService as TagsServiceInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\SearchResult;
use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\AddSynonymSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\ConvertToSynonymSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\CopySubtreeSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\CreateTagSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\DeleteTagSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\MergeTagsSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\MoveSubtreeSignal;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\UpdateTagSignal;

final class TagsService implements TagsServiceInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $service;

    /**
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    private $signalDispatcher;

    public function __construct(TagsServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        $this->service = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    public function loadTag(int $tagId, ?array $languages = null, bool $useAlwaysAvailable = true): Tag
    {
        return $this->service->loadTag($tagId, $languages, $useAlwaysAvailable);
    }

    public function loadTagList(array $tagIds, ?array $languages = null, bool $useAlwaysAvailable = true): array
    {
        return $this->service->loadTagList($tagIds, $languages, $useAlwaysAvailable);
    }

    public function loadTagByRemoteId(string $remoteId, ?array $languages = null, bool $useAlwaysAvailable = true): Tag
    {
        return $this->service->loadTagByRemoteId($remoteId, $languages, $useAlwaysAvailable);
    }

    public function loadTagByUrl(string $url, array $languages): Tag
    {
        return $this->service->loadTagByUrl($url, $languages);
    }

    public function loadTagChildren(?Tag $tag = null, int $offset = 0, int $limit = -1, ?array $languages = null, bool $useAlwaysAvailable = true): array
    {
        return $this->service->loadTagChildren($tag, $offset, $limit, $languages, $useAlwaysAvailable);
    }

    public function getTagChildrenCount(?Tag $tag = null, ?array $languages = null, bool $useAlwaysAvailable = true): int
    {
        return $this->service->getTagChildrenCount($tag, $languages, $useAlwaysAvailable);
    }

    public function loadTagsByKeyword(string $keyword, string $language, bool $useAlwaysAvailable = true, int $offset = 0, int $limit = -1): array
    {
        return $this->service->loadTagsByKeyword($keyword, $language, $useAlwaysAvailable, $offset, $limit);
    }

    public function getTagsByKeywordCount(string $keyword, string $language, bool $useAlwaysAvailable = true): int
    {
        return $this->service->getTagsByKeywordCount($keyword, $language, $useAlwaysAvailable);
    }

    public function searchTags(string $searchString, string $language, bool $useAlwaysAvailable = true, int $offset = 0, int $limit = -1): SearchResult
    {
        return $this->service->searchTags($searchString, $language, $useAlwaysAvailable, $offset, $limit);
    }

    public function loadTagSynonyms(Tag $tag, int $offset = 0, int $limit = -1, ?array $languages = null, bool $useAlwaysAvailable = true): array
    {
        return $this->service->loadTagSynonyms($tag, $offset, $limit, $languages, $useAlwaysAvailable);
    }

    public function getTagSynonymCount(Tag $tag, ?array $languages = null, bool $useAlwaysAvailable = true): int
    {
        return $this->service->getTagSynonymCount($tag, $languages, $useAlwaysAvailable);
    }

    public function getRelatedContent(Tag $tag, int $offset = 0, int $limit = -1, bool $returnContentInfo = true, array $additionalCriteria = [], array $sortClauses = []): array
    {
        return $this->service->getRelatedContent($tag, $offset, $limit, $returnContentInfo, $additionalCriteria, $sortClauses);
    }

    public function getRelatedContentCount(Tag $tag, array $additionalCriteria = []): int
    {
        return $this->service->getRelatedContentCount($tag, $additionalCriteria);
    }

    public function createTag(TagCreateStruct $tagCreateStruct): Tag
    {
        $returnValue = $this->service->createTag($tagCreateStruct);
        $this->signalDispatcher->emit(
            new CreateTagSignal(
                [
                    'tagId' => $returnValue->id,
                    'parentTagId' => $returnValue->parentTagId,
                    'keywords' => $returnValue->keywords,
                    'mainLanguageCode' => $returnValue->mainLanguageCode,
                    'alwaysAvailable' => $returnValue->alwaysAvailable,
                ]
            )
        );

        return $returnValue;
    }

    public function updateTag(Tag $tag, TagUpdateStruct $tagUpdateStruct): Tag
    {
        $returnValue = $this->service->updateTag($tag, $tagUpdateStruct);
        $this->signalDispatcher->emit(
            new UpdateTagSignal(
                [
                    'tagId' => $returnValue->id,
                    'keywords' => $returnValue->keywords,
                    'remoteId' => $returnValue->remoteId,
                    'mainLanguageCode' => $returnValue->mainLanguageCode,
                    'alwaysAvailable' => $returnValue->alwaysAvailable,
                ]
            )
        );

        return $returnValue;
    }

    public function addSynonym(SynonymCreateStruct $synonymCreateStruct): Tag
    {
        $returnValue = $this->service->addSynonym($synonymCreateStruct);
        $this->signalDispatcher->emit(
            new AddSynonymSignal(
                [
                    'tagId' => $returnValue->id,
                    'mainTagId' => $returnValue->mainTagId,
                    'keywords' => $returnValue->keywords,
                    'mainLanguageCode' => $returnValue->mainLanguageCode,
                    'alwaysAvailable' => $returnValue->alwaysAvailable,
                ]
            )
        );

        return $returnValue;
    }

    public function convertToSynonym(Tag $tag, Tag $mainTag): Tag
    {
        $returnValue = $this->service->convertToSynonym($tag, $mainTag);
        $this->signalDispatcher->emit(
            new ConvertToSynonymSignal(
                [
                    'tagId' => $returnValue->id,
                    'mainTagId' => $returnValue->mainTagId,
                ]
            )
        );

        return $returnValue;
    }

    public function mergeTags(Tag $tag, Tag $targetTag): void
    {
        $this->service->mergeTags($tag, $targetTag);
        $this->signalDispatcher->emit(
            new MergeTagsSignal(
                [
                    'tagId' => $tag->id,
                    'targetTagId' => $targetTag->id,
                ]
            )
        );
    }

    public function copySubtree(Tag $tag, ?Tag $targetParentTag = null): Tag
    {
        $returnValue = $this->service->copySubtree($tag, $targetParentTag);
        $this->signalDispatcher->emit(
            new CopySubtreeSignal(
                [
                    'sourceTagId' => $tag->id,
                    'targetParentTagId' => $targetParentTag instanceof Tag ?
                        $targetParentTag->id :
                        0,
                    'newTagId' => $returnValue->id,
                ]
            )
        );

        return $returnValue;
    }

    public function moveSubtree(Tag $tag, ?Tag $targetParentTag = null): Tag
    {
        $returnValue = $this->service->moveSubtree($tag, $targetParentTag);
        $this->signalDispatcher->emit(
            new MoveSubtreeSignal(
                [
                    'sourceTagId' => $tag->id,
                    'targetParentTagId' => $targetParentTag instanceof Tag ?
                        $targetParentTag->id :
                        0,
                ]
            )
        );

        return $returnValue;
    }

    public function deleteTag(Tag $tag): void
    {
        $this->service->deleteTag($tag);
        $this->signalDispatcher->emit(
            new DeleteTagSignal(
                [
                    'tagId' => $tag->id,
                ]
            )
        );
    }

    public function newTagCreateStruct(int $parentTagId, string $mainLanguageCode): TagCreateStruct
    {
        return $this->service->newTagCreateStruct($parentTagId, $mainLanguageCode);
    }

    public function newSynonymCreateStruct(int $mainTagId, string $mainLanguageCode): SynonymCreateStruct
    {
        return $this->service->newSynonymCreateStruct($mainTagId, $mainLanguageCode);
    }

    public function newTagUpdateStruct(): TagUpdateStruct
    {
        return $this->service->newTagUpdateStruct();
    }

    public function sudo(callable $callback, ?TagsServiceInterface $outerTagsService = null)
    {
        return $this->service->sudo($callback, $outerTagsService ?? $this);
    }
}
