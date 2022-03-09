<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\AdminUI\Menu;

use InvalidArgumentException;
use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use function is_array;
use function is_string;
use function mb_strpos;

final class RoutePrefixVoter implements VoterInterface
{
    private RequestStack $requestStack;

    private string $routePrefix;

    public function __construct(RequestStack $requestStack, string $routePrefix)
    {
        $this->requestStack = $requestStack;
        $this->routePrefix = $routePrefix;
    }

    public function matchItem(ItemInterface $item): ?bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return null;
        }

        $currentRoute = $request->attributes->get('_route') ?? '';
        if (mb_strpos($currentRoute, $this->routePrefix) !== 0) {
            return null;
        }

        $routes = (array) $item->getExtra('routes', []);

        foreach ($routes as $testedRoute) {
            if (is_string($testedRoute)) {
                $testedRoute = ['route' => $testedRoute];
            }

            if (!is_array($testedRoute)) {
                throw new InvalidArgumentException('Routes extra items must be strings or arrays.');
            }

            if ($this->isMatchingRoutePrefix($testedRoute)) {
                return true;
            }
        }

        return null;
    }

    private function isMatchingRoutePrefix(array $testedRoute): bool
    {
        if (!isset($testedRoute['route'])) {
            return false;
        }

        if (mb_strpos($testedRoute['route'], $this->routePrefix) !== 0) {
            return false;
        }

        return true;
    }
}
