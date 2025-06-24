<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Enums;

enum TagSortBy: string
{
    case Id = 'id';
    case Keyword = 'keyword';
    case Modified = 'modified';
    case Priority = 'priority';
}
