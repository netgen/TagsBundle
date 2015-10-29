<?php

namespace Netgen\TagsBundle\Matcher\Tag;

use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\Symfony\Matcher\ViewMatcherInterface;
use Netgen\TagsBundle\TagsServiceAware;

abstract class MultipleValued extends TagsServiceAware implements ViewMatcherInterface
{
    /**
     * @var array
     */
    protected $values;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * Registers the matching configuration for the matcher.
     *
     * @param mixed $matchingConfig
     *
     * @throws \InvalidArgumentException Should be thrown if $matchingConfig is not valid.
     */
    public function setMatchingConfig($matchingConfig)
    {
        $matchingConfig = !is_array($matchingConfig) ? array($matchingConfig) : $matchingConfig;
        $this->values = array_fill_keys($matchingConfig, true);
    }

    /**
     * Sets the translation helper.
     *
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     */
    public function setTranslationHelper(TranslationHelper $translationHelper)
    {
        $this->translationHelper = $translationHelper;
    }
}
