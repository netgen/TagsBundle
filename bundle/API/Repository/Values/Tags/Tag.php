<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Tags;

use DateTimeInterface;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

use function array_map;
use function count;
use function explode;
use function trim;

/**
 * Class representing a tag.
 *
 * @property-read int $id Tag ID
 * @property-read int $parentTagId Parent tag ID
 * @property-read int $mainTagId Main tag ID
 * @property-read string $keyword Convenience getter for $this->getKeyword() and BC layer
 * @property-read array<string, string|null> $keywords Tag keywords
 * @property-read int $depth The depth tag has in tag tree
 * @property-read string $pathString The path to this tag e.g. /1/6/21/42 where 42 is the current ID
 * @property-read array $path The IDs of all parents and tag itself
 * @property-read \DateTimeInterface $modificationDate Tag modification date
 * @property-read string $remoteId A global unique ID of the tag
 * @property-read bool $alwaysAvailable Indicates if the Tag object is shown in the main language if it is not present in an other requested language
 * @property-read string $mainLanguageCode The main language code of the Tag object
 * @property-read string[] $languageCodes List of languages in this Tag object
 * @property-read bool $isHidden Indicates if the Tag object is visible or not
 * @property-read bool $isInvisible Indicates if the Tag object is located under another hidden Tag object
 */
final class Tag extends ValueObject
{
    /**
     * Tag ID.
     */
    protected int $id;

    /**
     * Parent tag ID.
     */
    protected int $parentTagId;

    /**
     * Main tag ID.
     *
     * Zero if tag is not a synonym
     */
    protected int $mainTagId;

    /**
     * Returns the keywords in the available languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" ).
     *
     * @var array<string, string|null>
     */
    protected array $keywords = [];

    /**
     * The depth tag has in tag tree.
     */
    protected int $depth;

    /**
     * The path to this tag e.g. /1/6/21/42 where 42 is the current ID.
     */
    protected string $pathString;

    /**
     * @var int[]
     */
    protected array $path;

    /**
     * Tag modification date.
     */
    protected DateTimeInterface $modificationDate;

    /**
     * A global unique ID of the tag.
     */
    protected string $remoteId;

    /**
     * Indicates if the Tag object is shown in the main language if it is not present in an other requested language.
     */
    protected bool $alwaysAvailable;

    /**
     * The main language code of the Tag object.
     */
    protected string $mainLanguageCode;

    /**
     * List of languages in this Tag object.
     *
     * @var string[]
     */
    protected array $languageCodes = [];

    /**
     * The first matched keyword language among user provided prioritized languages on tag retrieval, or null
     * if none provided (all languages) or on main fallback.
     */
    protected ?string $prioritizedLanguageCode;

    /**
     * Indicates that the Tag is hidden.
     */
    protected bool $isHidden;

    /**
     * Indicates that the Tag object is not visible, being either hidden itself,
     * or implicitly hidden by parent or ancestor Tag object.
     */
    protected bool $isInvisible;

    /**
     * Construct object optionally with a set of properties.
     *
     * Readonly properties values must be set using $properties as they are not writable anymore
     * after object has been created.
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);

        if (isset($this->pathString) && $this->pathString !== '') {
            $this->path = array_map('intval', explode('/', trim($this->pathString, '/')));
        }
    }

    public function __get($property): mixed
    {
        if ($property === 'keyword') {
            return $this->getKeyword();
        }

        if (!isset($this->{$property})) {
            return null;
        }

        return parent::__get($property);
    }

    public function __isset($property): bool
    {
        if ($property === 'keyword') {
            return true;
        }

        if (!isset($this->{$property})) {
            return false;
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
     * @return array<string, string|null>
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
