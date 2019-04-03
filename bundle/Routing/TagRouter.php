<?php

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
    const TAG_URL_ROUTE_NAME = 'eztags_tag_url';

    const TAG_VIEW_ACTION_CONTROLLER = 'eztags.controller.tag_view:viewAction';

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \Netgen\TagsBundle\Routing\Generator\TagUrlGenerator
     */
    protected $generator;

    /**
     * @var \Symfony\Component\Routing\RequestContext
     */
    protected $requestContext;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $languages;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \Netgen\TagsBundle\Routing\Generator\TagUrlGenerator $generator
     * @param \Symfony\Component\Routing\RequestContext $requestContext
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        TagsService $tagsService,
        TagUrlGenerator $generator,
        RequestContext $requestContext = null,
        LoggerInterface $logger = null
    ) {
        $this->tagsService = $tagsService;
        $this->generator = $generator;
        $this->requestContext = $requestContext ?: new RequestContext();
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Sets the currently available languages to the router.
     *
     * @param array $languages
     */
    public function setLanguages(array $languages = null)
    {
        $this->languages = $languages !== null ? $languages : [];
    }

    /**
     * Tries to match a request with a set of routes.
     *
     * If the matcher can not find information, it must throw one of the exceptions documented
     * below.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request to match
     *
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException If no matching resource could be found
     *
     * @return array An array of parameters
     */
    public function matchRequest(Request $request)
    {
        $requestedPath = rawurldecode($request->attributes->get('semanticPathinfo', $request->getPathInfo()));
        $pathPrefix = $this->generator->getPathPrefix();

        if (mb_stripos($requestedPath, $pathPrefix) !== 0) {
            throw new ResourceNotFoundException();
        }

        $requestedPath = $this->removePathPrefix($requestedPath, $pathPrefix);
        $requestedPath = trim($requestedPath, '/');

        if (empty($requestedPath)) {
            throw new ResourceNotFoundException();
        }

        $tag = $this->tagsService->sudo(
            function (TagsService $tagsService) use ($requestedPath) {
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
    public function generate($name, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        // Direct access to Tag
        if ($name instanceof Tag) {
            return $this->generator->generate($name, $parameters, $referenceType);
        }

        // Normal route name
        if ($name === self::TAG_URL_ROUTE_NAME) {
            if (isset($parameters['tag']) || isset($parameters['tagId'])) {
                // Check if tag is a valid Tag object
                if (isset($parameters['tag']) && !$parameters['tag'] instanceof Tag) {
                    throw new LogicException(
                        "When generating a Tag route, 'tag' parameter must be a valid Netgen\\TagsBundle\\API\\Repository\\Values\\Tags\\Tag."
                    );
                }

                $tag = isset($parameters['tag']) ? $parameters['tag'] : $this->tagsService->loadTag($parameters['tagId']);
                unset($parameters['tag'], $parameters['tagId'], $parameters['viewType'], $parameters['layout']);

                return $this->generator->generate($tag, $parameters, $referenceType);
            }

            throw new InvalidArgumentException(
                "When generating a Tag route, either 'tag' or 'tagId' must be provided."
            );
        }

        throw new RouteNotFoundException('Could not match route');
    }

    /**
     * Gets the RouteCollection instance associated with this Router.
     *
     * @return \Symfony\Component\Routing\RouteCollection A RouteCollection instance
     */
    public function getRouteCollection()
    {
        return new RouteCollection();
    }

    /**
     * Sets the request context.
     *
     * @param \Symfony\Component\Routing\RequestContext $context The context
     */
    public function setContext(RequestContext $context)
    {
        $this->requestContext = $context;
        $this->generator->setRequestContext($context);
    }

    /**
     * Gets the request context.
     *
     * @return \Symfony\Component\Routing\RequestContext The context
     */
    public function getContext()
    {
        return $this->requestContext;
    }

    /**
     * Tries to match a URL path with a set of routes.
     *
     * If the matcher can not find information, it must throw one of the exceptions documented
     * below.
     *
     * @param string $pathinfo The path info to be parsed (raw format, i.e. not urldecoded)
     *
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException If the resource could not be found
     * @throws \Symfony\Component\Routing\Exception\MethodNotAllowedException If the resource was found but the request method is not allowed
     *
     * @return array An array of parameters
     */
    public function match($pathinfo)
    {
        throw new RuntimeException("The TagRouter doesn't support the match() method. Please use matchRequest() instead.");
    }

    /**
     * Whether this generator supports the supplied $name.
     *
     * This check does not need to look if the specific instance can be
     * resolved to a route, only whether the router can generate routes from
     * objects of this class.
     *
     * @param mixed $name The route "name" which may also be an object or anything
     *
     * @return bool
     */
    public function supports($name)
    {
        return $name instanceof Tag || $name === self::TAG_URL_ROUTE_NAME;
    }

    /**
     * Convert a route identifier (name, content object etc) into a string
     * usable for logging and other debug/error messages.
     *
     * @param mixed $name
     * @param array $parameters which should contain a content field containing a RouteReferrersReadInterface object
     *
     * @return string
     */
    public function getRouteDebugMessage($name, array $parameters = [])
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
     *
     * @param string $path
     * @param string $prefix
     *
     * @return string
     */
    protected function removePathPrefix($path, $prefix)
    {
        if ($prefix !== '/' && mb_stripos($path, $prefix) === 0) {
            $path = mb_substr($path, mb_strlen($prefix));
        }

        return $path;
    }
}
