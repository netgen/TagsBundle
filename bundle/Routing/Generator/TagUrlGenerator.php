<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Routing\Generator;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator;
use LogicException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Symfony\Component\Routing\RouterInterface;

/**
 * URL generator for Tag based links.
 *
 * @see \Netgen\TagsBundle\Routing\TagRouter
 */
class TagUrlGenerator extends Generator
{
    public const INTERNAL_TAG_ROUTE = '_eztagsTag';

    public const DEFAULT_PATH_PREFIX = '/tags/view';

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $defaultRouter;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    public function __construct(
        TagsService $tagsService,
        RouterInterface $defaultRouter,
        ConfigResolverInterface $configResolver
    ) {
        $this->tagsService = $tagsService;
        $this->defaultRouter = $defaultRouter;
        $this->configResolver = $configResolver;
    }

    /**
     * Generates the URL from $tag and $parameters.
     * Entries in $parameters will be added in the query string.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param array $parameters
     *
     * @return string
     */
    public function doGenerate($tag, array $parameters): string
    {
        if (isset($parameters['siteaccess'])) {
            // We generate for a different siteaccess, so potentially in a different language.
            $languages = $this->configResolver->getParameter('languages', null, $parameters['siteaccess']);
            unset($parameters['siteaccess']);
        } else {
            $languages = $this->configResolver->getParameter('languages');
        }

        $tagUrl = '';
        $isInternal = false;
        $originalTagId = $tagId = $tag->id;

        try {
            do {
                $tag = $this->tagsService->loadTag($tagId, $languages);

                $tagKeyword = null;
                foreach ($languages as $language) {
                    $tagKeyword = $tag->getKeyword($language);
                    if ($tagKeyword !== null) {
                        break;
                    }
                }

                if ($tagKeyword === null) {
                    if ($tag->alwaysAvailable) {
                        $tagKeyword = $tag->getKeyword();
                    }

                    if ($tagKeyword === null) {
                        throw new LogicException("Unknown error when generating URL for tag ID #{$originalTagId}");
                    }
                }

                $tagUrl = '/' . $tagKeyword . $tagUrl;

                $tagId = $tag->parentTagId;
            } while ($tagId > 0);
        } catch (NotFoundException $e) {
            $isInternal = true;
            $tagUrl = $this->defaultRouter->generate(
                self::INTERNAL_TAG_ROUTE,
                [
                    'tagId' => $originalTagId,
                ]
            );
        } catch (LogicException $e) {
            if ($this->logger !== null) {
                $this->logger->warning($e->getMessage());
            }

            $isInternal = true;
            $tagUrl = $this->defaultRouter->generate(
                self::INTERNAL_TAG_ROUTE,
                [
                    'tagId' => $originalTagId,
                ]
            );
        }

        $queryString = '';
        if (count($parameters) > 0) {
            $queryString = '?' . http_build_query($parameters, '', '&');
        }

        return (!$isInternal ? $this->getPathPrefix() : '') . '/' . trim($tagUrl, '/') . $queryString;
    }

    /**
     * Returns a configured path prefix for tag view page.
     */
    public function getPathPrefix(): string
    {
        $pathPrefix = $this->configResolver->getParameter('tag_view.path_prefix', 'eztags');
        $pathPrefix = trim($pathPrefix, '/');

        if ($pathPrefix === '') {
            return self::DEFAULT_PATH_PREFIX;
        }

        return '/' . $pathPrefix;
    }
}
