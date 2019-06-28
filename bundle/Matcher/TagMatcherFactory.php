<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Matcher;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Matcher\ClassNameMatcherFactory;
use eZ\Publish\Core\MVC\Symfony\Matcher\ViewMatcherInterface;
use eZ\Publish\Core\MVC\Symfony\View\View;
use InvalidArgumentException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\TagsServiceAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class TagMatcherFactory extends ClassNameMatcherFactory
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct(
        TagsService $tagsService,
        Repository $repository,
        ConfigResolverInterface $configResolver,
        ContainerInterface $container
    ) {
        $this->tagsService = $tagsService;
        $this->configResolver = $configResolver;
        $this->container = $container;

        parent::__construct($repository, 'Netgen\TagsBundle\Matcher\Tag');
    }

    public function match(View $view): ?array
    {
        $this->setMatchConfig($this->configResolver->getParameter('tag_view_match', 'eztags'));

        return parent::match($view);
    }

    protected function getMatcher($matcherIdentifier): ViewMatcherInterface
    {
        if ($this->container->has($matcherIdentifier)) {
            $matcher = $this->container->get($matcherIdentifier);
            if ($matcher instanceof ViewMatcherInterface) {
                return $matcher;
            }

            throw new InvalidArgumentException(
                'Matcher for tags must implement ' . ViewMatcherInterface::class . '.'
            );
        }

        $matcher = parent::getMatcher($matcherIdentifier);
        if (!$matcher instanceof ViewMatcherInterface) {
            throw new InvalidArgumentException(
                'Matcher for tags must implement ' . ViewMatcherInterface::class . '.'
            );
        }

        if ($matcher instanceof TagsServiceAwareInterface) {
            $matcher->setTagsService($this->tagsService);
        }

        return $matcher;
    }
}
