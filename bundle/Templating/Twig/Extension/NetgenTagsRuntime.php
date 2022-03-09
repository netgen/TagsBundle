<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Templating\Twig\Extension;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class NetgenTagsRuntime
{
    private TagsService $tagsService;

    private LanguageService $languageService;

    private ContentTypeService $contentTypeService;

    public function __construct(
        TagsService $tagsService,
        LanguageService $languageService,
        ContentTypeService $contentTypeService
    ) {
        $this->tagsService = $tagsService;
        $this->languageService = $languageService;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Returns tag keyword for provided tag ID.
     *
     * @param int $tagId
     */
    public function getTagKeyword($tagId): string
    {
        try {
            $tag = $this->tagsService->loadTag((int) $tagId);
        } catch (NotFoundException $e) {
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
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType|int $contentType
     */
    public function getContentTypeName($contentType): string
    {
        if (!$contentType instanceof ContentType) {
            try {
                $contentType = $this->contentTypeService->loadContentType($contentType);
            } catch (NotFoundException $e) {
                return '';
            }
        }

        return $contentType->getName() ?? '';
    }
}
