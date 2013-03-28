<?php

namespace EzSystems\TagsBundle\API\Repository\Values\Tags;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Class representing a tag
 *
 * @property-read integer $tagId Tag ID
 * @property-read string $keyword Tag keyword
 * @property-read integer $parentTagId Parent tag ID
 */
class Tag extends ValueObject
{
    /**
     * Tag ID
     *
     * @var integer
     */
    protected $tagId;

    /**
     * Tag keyword
     *
     * @var string
     */
    protected $keyword;

    /**
     * Parent tag ID
     *
     * @var integer
     */
    protected $parentTagId;
}
