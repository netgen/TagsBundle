<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Tags;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use function array_map;
use function count;
use function explode;
use function is_string;
use function trim;

/**
 * Class representing a tag.
 *
 * @property-read int $id Tag ID
 * @property-read int $parentTagId Parent tag ID
 * @property-read int $mainTagId Main tag ID
 * @property-read string $keyword Convenience getter for $this->getKeyword() and BC layer
 * @property-read string[] $keywords Tag keywords
 * @property-read int $depth The depth tag has in tag tree
 * @property-read string $pathString The path to this tag e.g. /1/6/21/42 where 42 is the current ID
 * @property-read array $path The IDs of all parents and tag itself
 * @property-read \DateTimeInterface $modificationDate Tag modification date
 * @property-read string $remoteId A global unique ID of the tag
 * @property-read bool $alwaysAvailable Indicates if the Tag object is shown in the main language if it is not present in an other requested language
 * @property-read string $mainLanguageCode The main language code of the Tag object
 * @property-read string[] $languageCodes List of languages in this Tag object
 */
final class Tag extends ValueObject
{
    /**
     * Tag ID.
     *
     * @var int
     */
    protected $id;

    /**
     * Parent tag ID.
     *
     * @var int
     */
    protected $parentTagId;

    /**
     * Main tag ID.
     *
     * Zero if tag is not a synonym
     *
     * @var int
     */
    protected $mainTagId;

    /**
     * Returns the keywords in the available languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" ).
     *
     * @var string[]
     */
    protected $keywords = [];

    /**
     * The depth tag has in tag tree.
     *
     * @var int
     */
    protected $depth;

    /**
     * The path to this tag e.g. /1/6/21/42 where 42 is the current ID.
     *
     * @var string
     */
    protected $pathString;

    /**
     * @var array
     */
    protected $path;

    /**
     * Tag modification date.
     *
     * @var \DateTimeInterface
     */
    protected $modificationDate;

    /**
     * A global unique ID of the tag.
     *
     * @var string
     */
    protected $remoteId;

    /**
     * Indicates if the Tag object is shown in the main language if it is not present in an other requested language.
     *
     * @var bool
     */
    protected $alwaysAvailable;

    /**
     * The main language code of the Tag object.
     *
     * @var string
     */
    protected $mainLanguageCode;

    /**
     * List of languages in this Tag object.
     *
     * @var string[]
     */
    protected $languageCodes = [];

    /**
     * The first matched keyword language among user provided prioritized languages on tag retrieval, or null
     * if none provided (all languages) or on main fallback.
     *
     * @var string|null
     */
    protected $prioritizedLanguageCode;

    /**
     * Construct object optionally with a set of properties.
     *
     * Readonly properties values must be set using $properties as they are not writable anymore
     * after object has been created.
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);

        if (is_string($this->pathString) && $this->pathString !== '') {
            $this->path = array_map(
                static function ($id): int {
                    return (int) $id;
                },
                explode('/', trim($this->pathString, '/'))
            );
        }
    }

    public function __get($property)
    {
        if ($property === 'keyword') {
            return $this->getKeyword();
        }

        return parent::__get($property);
    }

    public function __isset($property)
    {
        if ($property === 'keyword') {
            return true;
        }

        return parent::__isset($property);
    }

    /**
     * Returns the keyword in the given language.
     *
     * If no language is given, the keyword in main language of the tag if present, otherwise null
     */
    public function getKeyword(?string $languageCode = null): ?string
    {
        return $this->keywords[$languageCode ?? $this->prioritizedLanguageCode ?? $this->mainLanguageCode] ?? null;
    }

    /**
     * Returns tag translations sorted and filtered by provided list of language codes.
     *
     * @param string[] $languageCodes
     *
     * @return array
     */
    public function getKeywords(array $languageCodes): array
    {
        $keywords = [];

        foreach ($languageCodes as $languageCode) {
            if (isset($this->keywords[$languageCode])) {
                $keywords[$languageCode] = $this->keywords[$languageCode];
            }
        }

        if (count($keywords) === 0) {
            return [
                $this->mainLanguageCode => $this->getKeyword($this->mainLanguageCode),
            ];
        }

        return $keywords;
    }

    /**
     * Returns if the current tag has a parent or not.
     */
    public function hasParent(): bool
    {
        return $this->parentTagId !== 0;
    }

    /**
     * Returns if the current tag is a synonym or not.
     */
    public function isSynonym(): bool
    {
        return $this->mainTagId > 0;
    }

    protected function getProperties($dynamicProperties = ['keyword']): array
    {
        return parent::getProperties($dynamicProperties);
    }
}
