<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\SPI\Persistence\Tags;

use Ibexa\Contracts\Core\Persistence\ValueObject;

/**
 * This class represents a value for creating a tag.
 */
final class CreateStruct extends ValueObject
{
    /**
     * The ID of the parent tag under which the new tag should be created.
     *
     * @required
     */
    public int $parentTagId;

    /**
     * The main language code for the tag.
     *
     * @required
     */
    public string $mainLanguageCode;

    /**
     * Tag keywords in the target languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" ).
     *
     * @required
     *
     * @var string[]
     */
    public array $keywords;

    /**
     * A global unique ID of the tag.
     */
    public ?string $remoteId;

    /**
     * Indicates if the tag is shown in the main language if it's not present in an other requested language.
     */
    public ?bool $alwaysAvailable;
}
