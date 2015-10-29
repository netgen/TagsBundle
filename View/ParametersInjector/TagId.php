<?php

namespace Netgen\TagsBundle\View\ParametersInjector;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\View\TagValueView;

class TagId implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(ViewEvents::FILTER_VIEW_PARAMETERS => 'injectTagId');
    }

    /**
     * Injects the tag ID into the view. Required for BC with legacy controller actions.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent $event
     */
    public function injectTagId(FilterViewParametersEvent $event)
    {
        $view = $event->getView();
        if (!$view instanceof TagValueView) {
            return;
        }

        $tag = $view->getTag();
        if (!$tag instanceof Tag) {
            return;
        }

        $event->getParameterBag()->set('tagId', $tag->id);
    }
}
