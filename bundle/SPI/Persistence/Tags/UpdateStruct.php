<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\SPI\Persistence\Tags;

use Ibexa\Contracts\Core\Persistence\ValueObject;
use Netgen\TagsBundle\API\Repository\Values\Enums\TagSortBy;
use Netgen\TagsBundle\API\Repository\Values\Enums\TagSortOrder;

/**
 * This class represents a value for updating a tag.
 */
final class UpdateStruct extends ValueObject
{
    /**
     * Tag keywords in the target languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" ).
     *
     * @var string[]|null
     */
    public ?array $keywords;

    /**
     * A global unique ID of the tag.
     */
    public ?string $remoteId;

    /**
     * The main language code for the tag.
     */
    public ?string $mainLanguageCode;

    /**
     * Indicates if the tag is shown in the main language if it's not present in an other requested language.
     */
    public ?bool $alwaysAvailable;

    /**
     * Tag priority.
     *
     * Position of the Tag among its siblings when sorted by priority.
     */
    public ?int $priority;

    /**
     * Specifies by which property the child tags should be sorted on.
     */
    public ?TagSortBy $sortBy;

    /**
     * Specifies whether the sort order should be ascending or descending.
     */
    public ?TagSortOrder $sortOrder;
}
