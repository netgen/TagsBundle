<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\View\Builder\ParametersFilter;

use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use Netgen\TagsBundle\Routing\Generator\TagUrlGenerator;
use Netgen\TagsBundle\Routing\TagRouter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CurrentPage implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ViewEvents::FILTER_BUILDER_PARAMETERS => 'addCurrentPage',
        ];
    }

    /**
     * Adds the current page to the parameters.
     */
    public function addCurrentPage(FilterViewBuilderParametersEvent $event): void
    {
        $parameterBag = $event->getParameters();

        $route = $parameterBag->get('_route');
        if (!in_array($route, [TagRouter::TAG_URL_ROUTE_NAME, TagUrlGenerator::INTERNAL_TAG_ROUTE], true)) {
            return;
        }

        $parameterBag->set('page', (int) $event->getRequest()->get('page', 1));
    }
}
