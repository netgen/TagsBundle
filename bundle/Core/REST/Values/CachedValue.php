<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Values;

use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Rest\Value;

use function array_diff;
use function array_keys;
use function count;
use function implode;

final class CachedValue extends Value
{
    /**
     * @param mixed $value The value that gets cached
     * @param array<string, int|string> $cacheTags Associative array of cache tags. Example: array( 'tagId' => 42, 'tagKeyword' => 'Some tag|#eng-GB' ).
     */
    public function __construct(public mixed $value, public array $cacheTags = [])
    {
        $this->cacheTags = $this->checkCacheTags($cacheTags);
    }

    /**
     * Checks for unsupported cache tags.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If invalid cache tags are provided
     */
    private function checkCacheTags(array $tags): array
    {
        $invalidTags = array_diff(array_keys($tags), ['tagId', 'tagKeyword']);
        if (count($invalidTags) > 0) {
            throw new InvalidArgumentException(
                'cacheTags',
                'Unknown cache tag(s): ' . implode(', ', $invalidTags),
            );
        }

        return $tags;
    }
}
