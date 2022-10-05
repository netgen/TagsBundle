<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Tags;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;

use function array_key_exists;

abstract class TagStruct extends ValueObject
{
    /**
     * The main language code for the tag.
     *
     * Required when creating a tag.
     */
    public ?string $mainLanguageCode;

    /**
     * A global unique ID of the tag.
     */
    public ?string $remoteId = null;

    /**
     * Tag keywords in the target languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" ).
     *
     * Required when creating a tag.
     *
     * @var string[]
     */
    protected array $keywords = [];

    /**
     * Returns keywords available in the struct.
     *
     * @return string[]
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /**
     * Gets a keyword from keyword collection.
     *
     * If language is not given, the main language is used.
     */
    public function getKeyword(?string $language = null): ?string
    {
        return $this->keywords[$language ?? $this->mainLanguageCode] ?? null;
    }

    /**
     * Adds a keyword to keyword collection.
     */
    public function setKeyword(string $keyword, ?string $language = null): void
    {
        $this->keywords[$language ?? $this->mainLanguageCode] = $keyword;
    }

    /**
     * Removes the keyword from keyword collection.
     */
    public function removeKeyword(string $language): void
    {
        if (!array_key_exists($language, $this->keywords)) {
            return;
        }

        unset($this->keywords[$language]);
    }
}
