<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Repository;

use DateTimeImmutable;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag as SPITag;

final class TagsMapper
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    private $languageHandler;

    public function __construct(LanguageHandler $languageHandler)
    {
        $this->languageHandler = $languageHandler;
    }

    public function buildTagDomainObject(SPITag $spiTag, array $prioritizedLanguages = []): Tag
    {
        return $this->buildTagDomainList([$spiTag], $prioritizedLanguages)[$spiTag->id];
    }

    public function buildTagDomainList(array $spiTags, array $prioritizedLanguages = []): array
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

            $prioritizedLanguageCode = null;
            if (count($prioritizedLanguages) > 0) {
                foreach ($prioritizedLanguages as $prioritizedLanguage) {
                    if (in_array($prioritizedLanguage, $languageCodes, true)) {
                        $prioritizedLanguageCode = $prioritizedLanguage;

                        break;
                    }
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
                    'prioritizedLanguageCode' => $prioritizedLanguageCode,
                ]
            );
        }

        return $tags;
    }
}
