<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Routing;

use InvalidArgumentException;
use LogicException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Routing\Generator\TagUrlGenerator;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Cmf\Component\Routing\ChainedRouterInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

class TagRouter implements ChainedRouterInterface, RequestMatcherInterface
{
    public const TAG_URL_ROUTE_NAME = 'eztags_tag_url';

    public const TAG_VIEW_ACTION_CONTROLLER = 'eztags.controller.tag_view:viewAction';

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var \Netgen\TagsBundle\Routing\Generator\TagUrlGenerator
     */
    private $generator;

    /**
     * @var \Symfony\Component\Routing\RequestContext
     */
    private $requestContext;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $languages;

    public function __construct(
        TagsService $tagsService,
        TagUrlGenerator $generator,
        ?RequestContext $requestContext = null,
        ?LoggerInterface $logger = null
    ) {
        $this->tagsService = $tagsService;
        $this->generator = $generator;
        $this->requestContext = $requestContext ?: new RequestContext();
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Sets the currently available languages to the router.
     */
    public function setLanguages(?array $languages = null)
    {
        $this->languages = $languages ?? [];
    }

    public function matchRequest(Request $request): array
    {
        $requestedPath = rawurldecode($request->attributes->get('semanticPathinfo', $request->getPathInfo()));
        $pathPrefix = $this->generator->getPathPrefix();

        if (mb_stripos($requestedPath, $pathPrefix) !== 0) {
            throw new ResourceNotFoundException();
        }

        $requestedPath = $this->removePathPrefix($requestedPath, $pathPrefix);
        $requestedPath = trim($requestedPath, '/');

        if ($requestedPath === '') {
            throw new ResourceNotFoundException();
        }

        $tag = $this->tagsService->sudo(
            function (TagsService $tagsService) use ($requestedPath): Tag {
                return $tagsService->loadTagByUrl(
                    $requestedPath,
                    $this->languages
                );
            }
        );

        // We specifically pass tag ID so tag view builder will reload the tag and check for permissions
        // Unfortunately, since at this point user is still anonymous (why!?), this is the best we can do
        $params = [
            '_route' => self::TAG_URL_ROUTE_NAME,
            '_controller' => static::TAG_VIEW_ACTION_CONTROLLER,
            'tagId' => $tag->id,
        ];

        $request->attributes->set('tagId', $tag->id);

        $this->logger->info(
            "TagRouter matched tag #{$tag->id}. Forwarding to tag view controller"
        );

        return $params;
    }

    /**
     * Generates a URL for a tag, from the given parameters.
     *
     * It is possible to directly pass a Tag object as the route name, as the ChainRouter allows it through ChainedRouterInterface
     *
     * If $name is a route name, the "tag" key in $parameters must be set to a valid Netgen\TagsBundle\API\Repository\Values\Tags\Tag object.
     * "tagId" can also be provided.
     *
     * If the generator is not able to generate the URL, it must throw the RouteNotFoundException as documented below.
     *
     * @param string|\Netgen\TagsBundle\API\Repository\Values\Tags\Tag $name The name of the route or a Tag instance
     * @param mixed $parameters An array of parameters
     * @param int $referenceType The type of reference to be generated (one of the constants)
     *
     * @throws \LogicException
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \InvalidArgumentException
     *
     * @return string The generated URL
     */
    public function generate($name, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        // Direct access to Tag
        if ($name instanceof Tag) {
            return $this->generator->generate($name, $parameters, $referenceType);
        }

        // Normal route name
        if ($name === self::TAG_URL_ROUTE_NAME) {
            if (isset($parameters['tag']) || isset($parameters['tagId'])) {
                $tag = $parameters['tag'] ?? null;
                // Check if tag is a valid Tag object
                if ($tag !== null && !$tag instanceof Tag) {
                    throw new LogicException(
                        "When generating a Tag route, 'tag' parameter must be a valid Netgen\\TagsBundle\\API\\Repository\\Values\\Tags\\Tag."
                    );
                }

                $tag = $tag ?? $this->tagsService->loadTag($parameters['tagId']);
                unset($parameters['tag'], $parameters['tagId'], $parameters['viewType'], $parameters['layout']);

                return $this->generator->generate($tag, $parameters, $referenceType);
            }

            throw new InvalidArgumentException(
                "When generating a Tag route, either 'tag' or 'tagId' must be provided."
            );
        }

        throw new RouteNotFoundException('Could not match route');
    }

    public function getRouteCollection(): RouteCollection
    {
        return new RouteCollection();
    }

    public function setContext(RequestContext $context): void
    {
        $this->requestContext = $context;
        $this->generator->setRequestContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->requestContext;
    }

    public function match($pathinfo): array
    {
        throw new RuntimeException("The TagRouter doesn't support the match() method. Please use matchRequest() instead.");
    }

    public function supports($name): bool
    {
        return $name instanceof Tag || $name === self::TAG_URL_ROUTE_NAME;
    }

    public function getRouteDebugMessage($name, array $parameters = []): string
    {
        if ($name instanceof RouteObjectInterface) {
            return 'Route with key ' . $name->getRouteKey();
        }

        if ($name instanceof SymfonyRoute) {
            return 'Route with pattern ' . $name->getPath();
        }

        return $name;
    }

    /**
     * Removes prefix from path.
     *
     * Checks for presence of $prefix and removes it from $path if found.
     */
    private function removePathPrefix(string $path, string $prefix): string
    {
        if ($prefix !== '/' && mb_stripos($path, $prefix) === 0) {
            $path = mb_substr($path, mb_strlen($prefix));
        }

        return $path;
    }
}
