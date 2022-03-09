<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Matcher\Tag;

use Ibexa\Core\MVC\Symfony\Matcher\ViewMatcherInterface;
use Netgen\TagsBundle\TagsServiceAwareInterface;
use Netgen\TagsBundle\TagsServiceAwareTrait;
use function array_fill_keys;
use function is_array;

abstract class MultipleValued implements ViewMatcherInterface, TagsServiceAwareInterface
{
    use TagsServiceAwareTrait;

    /**
     * @var mixed[]
     */
    protected array $values = [];

    public function setMatchingConfig($matchingConfig): void
    {
        $matchingConfig = !is_array($matchingConfig) ? [$matchingConfig] : $matchingConfig;
        $this->values = array_fill_keys($matchingConfig, true);
    }
}
