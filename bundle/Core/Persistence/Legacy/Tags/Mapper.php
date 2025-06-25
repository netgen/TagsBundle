<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Tags;

use Ibexa\Contracts\Core\Persistence\Content\Language\Handler as LanguageHandler;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;
use Netgen\TagsBundle\API\Repository\Values\Enums\TagSortBy;
use Netgen\TagsBundle\API\Repository\Values\Enums\TagSortOrder;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag;
use Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo;

use function array_values;

/**
 * @final
 */
class Mapper
{
    public function __construct(
        private readonly LanguageHandler $languageHandler,
        private readonly LanguageMaskGenerator $languageMaskGenerator,
        private readonly ConfigResolverInterface $configResolver,
    ) {}

    /**
     * Creates a tag from a $data row.
     */
    public function createTagInfoFromRow(array $row): TagInfo
    {
        $tagInfo = new TagInfo();

        $tagInfo->id = (int) $row['id'];
        $tagInfo->parentTagId = (int) $row['parent_id'];
        $tagInfo->mainTagId = (int) $row['main_tag_id'];
        $tagInfo->depth = (int) $row['depth'];
        $tagInfo->pathString = $row['path_string'];
        $tagInfo->modificationDate = (int) $row['modified'];
        $tagInfo->remoteId = $row['remote_id'];
        $tagInfo->alwaysAvailable = (bool) ((int) $row['language_mask'] & 1);
        $tagInfo->mainLanguageCode = $this->languageHandler->load($row['main_language_id'])->languageCode;
        $tagInfo->languageIds = $this->languageMaskGenerator->extractLanguageIdsFromMask((int) $row['language_mask']);
        $tagInfo->priority = (int) $row['priority'];

        if ((int) $row['id'] === 0) {
            $tagInfo->sortBy = TagSortBy::tryFrom($row['sort_by'])
                ?? TagSortBy::from($this->configResolver->getParameter('sort.root.by', 'netgen_tags'));
            $tagInfo->sortOrder = TagSortOrder::tryFrom($row['sort_order'])
                ?? TagSortOrder::from($this->configResolver->getParameter('sort.root.order', 'netgen_tags'));
        } else {
            $tagInfo->sortBy = TagSortBy::tryFrom($row['sort_by'])
                ?? TagSortBy::from($this->configResolver->getParameter('sort.by', 'netgen_tags'));
            $tagInfo->sortOrder = TagSortOrder::tryFrom($row['sort_order'])
                ?? TagSortOrder::from($this->configResolver->getParameter('sort.order', 'netgen_tags'));
        }

        return $tagInfo;
    }

    /**
     * Extracts a Tag object from $row.
     */
    public function extractTagListFromRows(array $rows): array
    {
        $tagList = [];
        foreach ($rows as $row) {
            $tagId = (int) $row['id'];
            if (!isset($tagList[$tagId])) {
                $tag = new Tag();
                $tag->id = (int) $row['id'];
                $tag->parentTagId = (int) $row['parent_id'];
                $tag->mainTagId = (int) $row['main_tag_id'];
                $tag->keywords = [];
                $tag->depth = (int) $row['depth'];
                $tag->pathString = $row['path_string'];
                $tag->modificationDate = (int) $row['modified'];
                $tag->remoteId = $row['remote_id'];
                $tag->alwaysAvailable = (bool) ((int) $row['language_mask'] & 1);
                $tag->mainLanguageCode = $this->languageHandler->load($row['main_language_id'])->languageCode;
                $tag->languageIds = $this->languageMaskGenerator->extractLanguageIdsFromMask((int) $row['language_mask']);
                $tag->priority = (int) $row['priority'];

                if ((int) $row['id'] === 0) {
                    $tag->sortBy = TagSortBy::tryFrom($row['sort_by'])
                        ?? TagSortBy::from($this->configResolver->getParameter('sort.root.by', 'netgen_tags'));
                    $tag->sortOrder = TagSortOrder::tryFrom($row['sort_order'])
                        ?? TagSortOrder::from($this->configResolver->getParameter('sort.root.order', 'netgen_tags'));
                } else {
                    $tag->sortBy = TagSortBy::tryFrom($row['sort_by'])
                        ?? TagSortBy::from($this->configResolver->getParameter('sort.by', 'netgen_tags'));
                    $tag->sortOrder = TagSortOrder::tryFrom($row['sort_order'])
                        ?? TagSortOrder::from($this->configResolver->getParameter('sort.order', 'netgen_tags'));
                }

                $tagList[$tagId] = $tag;
            }

            $tagList[$tagId]->keywords[$row['locale']] ??= $row['keyword'];
        }

        return array_values($tagList);
    }
}
