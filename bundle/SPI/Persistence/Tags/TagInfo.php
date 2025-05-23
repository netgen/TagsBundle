<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\SPI\Persistence\Tags;

use Ibexa\Contracts\Core\Persistence\ValueObject;

/**
 * Class representing a tag info (basically a tag without keywords).
 */
final class TagInfo extends ValueObject
{
    /**
     * Tag ID.
     */
    public int $id;

    /**
     * Parent tag ID.
     */
    public int $parentTagId;

    /**
     * Main tag ID.
     *
     * Zero if tag is not a synonym
     */
    public int $mainTagId;

    /**
     * The depth tag has in tag tree.
     */
    public int $depth;

    /**
     * The path to this tag e.g. /1/6/21/42 where 42 is the current ID.
     */
    public string $pathString;

    /**
     * Tag modification date as a UNIX timestamp.
     */
    public int $modificationDate;

    /**
     * A global unique ID of the tag.
     */
    public string $remoteId;

    /**
     * Indicates if the tag is shown in the main language if its not present in an other requested language.
     */
    public bool $alwaysAvailable;

    /**
     * The main language code of the tag.
     */
    public string $mainLanguageCode;

    /**
     * List of languages in this tag.
     *
     * @var int[]
     */
    public array $languageIds = [];

    /**
     * Indicates if the tag is visible or not
     */
    public bool $isHidden;

    /**
     * Indicates if the tag is located under another hidden tag
     */
    public bool $isInvisible;
}
