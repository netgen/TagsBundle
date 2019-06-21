<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\Value;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value;

/**
 * Struct that stores extra value information for a TagKeyword criterion object.
 */
class TagKeywordValue extends Value
{
    /**
     * One or more languages to match in. If empty, Criterion will match in all available languages.
     *
     * @var string[]|null
     */
    public $languages;

    /**
     * Whether to use always available flag in addition to provided languages.
     *
     * @var bool
     */
    public $useAlwaysAvailable = true;

    public function __construct(?array $languages = null, bool $useAlwaysAvailable = true)
    {
        $this->languages = $languages;
        $this->useAlwaysAvailable = $useAlwaysAvailable;
    }
}
