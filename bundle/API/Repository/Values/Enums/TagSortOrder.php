<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Enums;

enum TagSortOrder: string
{
    case Ascending = 'asc';
    case Descending = 'desc';
}
