<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Matcher;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\Symfony\Matcher\ClassNameMatcherFactory;
use eZ\Publish\Core\MVC\Symfony\Matcher\ViewMatcherInterface;
use InvalidArgumentException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Matcher\Tag\MultipleValued;
use Netgen\TagsBundle\TagsServiceAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class TagMatcherFactory extends ClassNameMatcherFactory
{
    use ContainerAwareTrait;

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    private $translationHelper;

    public function __construct(
        TagsService $tagsService,
        TranslationHelper $translationHelper,
        Repository $repository
    ) {
        $this->tagsService = $tagsService;
        $this->translationHelper = $translationHelper;

        parent::__construct($repository, 'Netgen\TagsBundle\Matcher\Tag');
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

        if ($matcher instanceof MultipleValued) {
            $matcher->setTranslationHelper($this->translationHelper);
        }

        return $matcher;
    }
}
