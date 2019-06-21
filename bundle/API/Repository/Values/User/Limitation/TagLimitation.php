<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation;

final class TagLimitation extends Limitation
{
    public const TAG = 'Tag';

    public function getIdentifier(): string
    {
        return self::TAG;
    }
}
