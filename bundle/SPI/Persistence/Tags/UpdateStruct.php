<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\SPI\Persistence\Tags;

use Ibexa\Contracts\Core\Persistence\ValueObject;

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
}
