<?php

namespace Netgen\TagsBundle\PlatformAdminUI\Menu;

use InvalidArgumentException;
use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;

class RoutePrefixVoter implements VoterInterface
{
    /**
     * @var string
     */
    private $routePrefix;

    public function __construct($routePrefix)
    {
        $this->routePrefix = $routePrefix;
    }

    public function matchItem(ItemInterface $item)
    {
        $routes = (array) $item->getExtra('routes', array());

        foreach ($routes as $testedRoute) {
            if (is_string($testedRoute)) {
                $testedRoute = array('route' => $testedRoute);
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

    private function isMatchingRoutePrefix(array $testedRoute)
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
