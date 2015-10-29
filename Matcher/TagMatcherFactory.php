<?php

namespace Netgen\TagsBundle\Matcher;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory;
use eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface;
use eZ\Publish\Core\MVC\Symfony\Matcher\ViewMatcherInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Matcher\Tag\MultipleValued;
use Netgen\TagsBundle\TagsServiceAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use InvalidArgumentException;

class TagMatcherFactory extends AbstractMatcherFactory implements SiteAccessAware, ContainerAwareInterface
{
    const MATCHER_RELATIVE_NAMESPACE = 'Netgen\\TagsBundle\\Matcher\\Tag';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct(
        TagsService $tagsService,
        ConfigResolverInterface $configResolver,
        TranslationHelper $translationHelper,
        Repository $repository
    ) {
        $this->tagsService = $tagsService;
        $this->configResolver = $configResolver;
        $this->translationHelper = $translationHelper;

        parent::__construct(
            $repository,
            $this->configResolver->getParameter('tag_view_match', 'eztags')
        );
    }

    /**
     * Sets the container.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Changes internal configuration to use the one from passed site access.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteAccess
     */
    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
        if ($siteAccess === null) {
            return;
        }

        $this->matchConfig = $this->configResolver->getParameter(
            'tag_view_match',
            'eztags',
            $siteAccess->name
        );
    }

    /**
     * Returns the matcher object.
     *
     * @param string $matcherIdentifier The matcher class.
     *        If it begins with a '\' it means it's a fully qualified class name,
     *        otherwise it is relative to static::MATCHER_RELATIVE_NAMESPACE namespace (if available).
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

    /**
     * Checks if $valueObject matches $matcher rules.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface $matcher
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $valueObject
     *
     * @return bool
     */
    protected function doMatch(MatcherInterface $matcher, View $valueObject)
    {
    }
}
