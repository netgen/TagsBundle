<?php

namespace Netgen\TagsBundle\View\Builder\ParametersFilter;

use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent;
use Netgen\TagsBundle\Routing\Generator\TagUrlGenerator;
use Netgen\TagsBundle\Routing\TagRouter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents as ViewEvents;

class CurrentPage implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            ViewEvents::FILTER_BUILDER_PARAMETERS => 'addCurrentPage',
        );
    }

    /**
     * Adds the current page to the parameters.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent $event
     */
    public function addCurrentPage(FilterViewBuilderParametersEvent $event)
    {
        $parameterBag = $event->getParameters();

        $route = $parameterBag->get('_route');
        if (!in_array($route, array(TagRouter::TAG_URL_ROUTE_NAME, TagUrlGenerator::INTERNAL_TAG_ROUTE))) {
            return;
        }

        $parameterBag->set('page', (int)$event->getRequest()->get('page', 1));
    }
}
