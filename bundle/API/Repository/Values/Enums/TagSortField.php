<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Enums;

enum TagSortField: string
{
    case ID = 'id';
    case KEYWORD = 'keyword';
    case MODIFIED = 'modified';
    case PRIORITY = 'priority';
}
