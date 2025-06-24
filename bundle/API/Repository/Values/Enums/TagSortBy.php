<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Enums;

enum TagSortBy: string
{
    case ID = 'id';
    case KEYWORD = 'keyword';
    case MODIFIED = 'modified';
    case PRIORITY = 'priority';
}
