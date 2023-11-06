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
use function str_starts_with;

final class RoutePrefixVoter implements VoterInterface
{
    public function __construct(private RequestStack $requestStack, private string $routePrefix) {}

    public function matchItem(ItemInterface $item): ?bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return null;
        }

        $currentRoute = $request->attributes->get('_route') ?? '';
        if (!str_starts_with($currentRoute, $this->routePrefix)) {
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

        if (!str_starts_with($testedRoute['route'], $this->routePrefix)) {
            return false;
        }

        return true;
    }
}
