<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Tags;

/**
 * This class represents a value for updating a tag.
 */
final class TagUpdateStruct extends TagStruct
{
    /**
     * Indicates if the tag is shown in the main language if it's not present in an other requested language.
     *
     * @var bool
     */
    public $alwaysAvailable;
}
