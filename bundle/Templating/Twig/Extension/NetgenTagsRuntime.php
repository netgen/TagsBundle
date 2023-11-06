<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Templating\Twig\Extension;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Netgen\TagsBundle\API\Repository\TagsService;

final class NetgenTagsRuntime
{
    public function __construct(
        private TagsService $tagsService,
        private LanguageService $languageService,
        private ContentTypeService $contentTypeService,
    ) {}

    /**
     * Returns tag keyword for provided tag ID.
     */
    public function getTagKeyword(int|string $tagId): string
    {
        try {
            $tag = $this->tagsService->loadTag((int) $tagId);
        } catch (NotFoundException) {
            return '';
        }

        return $tag->getKeyword() ?? '';
    }

    /**
     * Returns the language name for specified language code.
     */
    public function getLanguageName(string $languageCode): string
    {
        return $this->languageService->loadLanguage($languageCode)->name;
    }

    /**
     * Returns content type name for provided content type ID or content type object.
     */
    public function getContentTypeName(ContentType|int $contentType): string
    {
        if (!$contentType instanceof ContentType) {
            try {
                $contentType = $this->contentTypeService->loadContentType($contentType);
            } catch (NotFoundException) {
                return '';
            }
        }

        return $contentType->getName() ?? '';
    }
}
