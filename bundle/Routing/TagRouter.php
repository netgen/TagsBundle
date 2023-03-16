<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Routing;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
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

use function is_object;
use function mb_strlen;
use function mb_substr;
use function str_starts_with;
use function trim;
use function urldecode;

final class TagRouter implements ChainedRouterInterface, RequestMatcherInterface
{
    public const TAG_URL_ROUTE_NAME = 'netgen_tags.tag.url';

    public const TAG_VIEW_ACTION_CONTROLLER = 'netgen_tags.controller.tag_view:viewAction';

    public function __construct(
        private TagsService $tagsService,
        private TagUrlGenerator $generator,
        private ConfigResolverInterface $configResolver,
        private RequestContext $requestContext = new RequestContext(),
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function matchRequest(Request $request): array
    {
        $requestedPath = urldecode($request->attributes->get('semanticPathinfo', $request->getPathInfo()));
        $pathPrefix = $this->generator->getPathPrefix();

        if (!str_starts_with($requestedPath, $pathPrefix)) {
            throw new ResourceNotFoundException('Route not found');
        }

        $requestedPath = $this->removePathPrefix($requestedPath, $pathPrefix);
        $requestedPath = trim($requestedPath, '/');

        if ($requestedPath === '') {
            throw new ResourceNotFoundException('Route not found');
        }

        $tag = $this->tagsService->sudo(
            fn (TagsService $tagsService): Tag => $tagsService->loadTagByUrl(
                $requestedPath,
                $this->configResolver->getParameter('languages'),
            ),
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
            "TagRouter matched tag #{$tag->id}. Forwarding to tag view controller",
        );

        return $params;
    }

    public function generate(string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        // Support using Tag object with ibexa_url / ibexa_path Twig functions
        if (
            ($name === '' || $name === 'cmf_routing_object')
            && $this->supportsObject($parameters[RouteObjectInterface::ROUTE_OBJECT] ?? null)
        ) {
            $tag = $parameters[RouteObjectInterface::ROUTE_OBJECT];
            unset($parameters[RouteObjectInterface::ROUTE_OBJECT]);

            return $this->generator->generate($tag, $parameters, $referenceType);
        }

        // Normal route name
        if ($name === self::TAG_URL_ROUTE_NAME) {
            if (isset($parameters['tag']) || isset($parameters['tagId'])) {
                $tag = $parameters['tag'] ?? null;
                // Check if tag is a valid Tag object
                if ($tag !== null && !$tag instanceof Tag) {
                    throw new LogicException(
                        "When generating a Tag route, 'tag' parameter must be a valid Netgen\\TagsBundle\\API\\Repository\\Values\\Tags\\Tag.",
                    );
                }

                $tag = $tag ?? $this->tagsService->loadTag($parameters['tagId']);
                unset($parameters['tag'], $parameters['tagId'], $parameters['viewType'], $parameters['layout']);

                return $this->generator->generate($tag, $parameters, $referenceType);
            }

            throw new InvalidArgumentException(
                "When generating a Tag route, either 'tag' or 'tagId' must be provided.",
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

    public function match(string $pathinfo): array
    {
        throw new RuntimeException("The TagRouter doesn't support the match() method. Please use matchRequest() instead.");
    }

    public function supports(mixed $name): bool
    {
        if (is_object($name)) {
            return $this->supportsObject($name);
        }

        return $name === '' || $name === 'cmf_routing_object' || $name === self::TAG_URL_ROUTE_NAME;
    }

    public function supportsObject(?object $object): bool
    {
        return $object instanceof Tag;
    }

    public function getRouteDebugMessage(mixed $name, array $parameters = []): string
    {
        return match (true) {
            $name instanceof RouteObjectInterface => 'Route with key ' . $name->getRouteKey(),
            $name instanceof SymfonyRoute => 'Route with pattern ' . $name->getPath(),
            default => $name,
        };
    }

    /**
     * Removes prefix from path.
     *
     * Checks for presence of $prefix and removes it from $path if found.
     */
    private function removePathPrefix(string $path, string $prefix): string
    {
        if ($prefix !== '/' && str_starts_with($path, $prefix)) {
            $path = mb_substr($path, mb_strlen($prefix));
        }

        return $path;
    }
}
