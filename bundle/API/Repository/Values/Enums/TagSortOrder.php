<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\Enums;

enum TagSortOrder: string
{
    case ASC = 'asc';
    case DESC = 'desc';
}
