<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Matcher\Tag;

use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\Symfony\Matcher\ViewMatcherInterface;
use Netgen\TagsBundle\TagsServiceAwareInterface;
use Netgen\TagsBundle\TagsServiceAwareTrait;

abstract class MultipleValued implements ViewMatcherInterface, TagsServiceAwareInterface
{
    use TagsServiceAwareTrait;

    /**
     * @var array
     */
    protected $values;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    public function setMatchingConfig($matchingConfig): void
    {
        $matchingConfig = !is_array($matchingConfig) ? [$matchingConfig] : $matchingConfig;
        $this->values = array_fill_keys($matchingConfig, true);
    }

    public function setTranslationHelper(TranslationHelper $translationHelper): void
    {
        $this->translationHelper = $translationHelper;
    }
}
