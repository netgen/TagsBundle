<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Tags;

/**
 * This class represents a value for creating a tag.
 */
final class TagCreateStruct extends TagStruct
{
    /**
     * The ID of the parent tag under which the new tag should be created.
     *
     * Required
     *
     * @var int
     */
    public $parentTagId;

    /**
     * Indicates if the tag is shown in the main language if it's not present in an other requested language.
     *
     * @var bool
     */
    public $alwaysAvailable = true;
}
