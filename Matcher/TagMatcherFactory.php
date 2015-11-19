<?php

namespace Netgen\TagsBundle\Matcher;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\Symfony\Matcher\ClassNameMatcherFactory;
use eZ\Publish\Core\MVC\Symfony\Matcher\ViewMatcherInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Matcher\Tag\MultipleValued;
use Netgen\TagsBundle\TagsServiceAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use InvalidArgumentException;

class TagMatcherFactory extends ClassNameMatcherFactory
{
    use ContainerAwareTrait;

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct(
        TagsService $tagsService,
        TranslationHelper $translationHelper,
        Repository $repository
    ) {
        $this->tagsService = $tagsService;
        $this->translationHelper = $translationHelper;

        parent::__construct($repository, 'Netgen\TagsBundle\Matcher\Tag');
    }

    /**
     * Returns the matcher object.
     *
     * @param string $matcherIdentifier The matcher class.
     *        If it begins with a '\' it means it's a fully qualified class name,
     *        otherwise it is relative to provided namespace (if available).
     *
     * @throws \InvalidArgumentException If no matcher could be found
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Matcher\ViewMatcherInterface
     */
    protected function getMatcher($matcherIdentifier)
    {
        if ($this->container->has($matcherIdentifier)) {
            return $this->container->get($matcherIdentifier);
        }

        $matcher = parent::getMatcher($matcherIdentifier);
        if (!$matcher instanceof ViewMatcherInterface) {
            throw new InvalidArgumentException(
                'Matcher for tags must implement eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ViewMatcherInterface.'
            );
        }

        if ($matcher instanceof TagsServiceAwareInterface) {
            $matcher->setTagsService($this->tagsService);
        }

        if ($matcher instanceof MultipleValued) {
            $matcher->setTranslationHelper($this->translationHelper);
        }

        return $matcher;
    }
}
