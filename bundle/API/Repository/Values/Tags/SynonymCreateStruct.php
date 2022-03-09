<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Tags;

/**
 * This class represents a value for creating a synonym.
 */
final class SynonymCreateStruct extends TagStruct
{
    /**
     * The ID of the main tag for which the new synonym should be created.
     *
     * Required.
     */
    public int $mainTagId;

    /**
     * Indicates if the tag is shown in the main language if it's not present in an other requested language.
     */
    public bool $alwaysAvailable = true;
}
