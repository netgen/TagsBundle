<?php

namespace EzSystems\TagsBundle\API\Repository\Values\Tags;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a queried tag list holding a total count and a partial list of tags (by offset/limit parameters)
 *
 * @property-read integer $totalCount The total count of found tags
 * @property-read \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag[] $tags The partial list of tags controlled by offset/limit
 **/
class TagList extends ValueObject
{
    /**
     * The total count of found tags
     *
     * @var integer
     */
    protected $totalCount;

    /**
     * The partial list of tags controlled by offset/limit
     *
     * @var \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag[]
     */
    protected $tags;
}
