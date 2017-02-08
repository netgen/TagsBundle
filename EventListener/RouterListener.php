<?php

namespace Netgen\TagsBundle\EventListener;

use eZ\Bundle\EzPublishLegacyBundle\Routing\FallbackRouter;
use Netgen\TagsBundle\Routing\Generator\TagUrlGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RouterListener implements EventSubscriberInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $enableTagRouter = true;

    /**
     * Constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Sets the flag that enables of disables the tag router.
     *
     * @param bool $enableTagRouter
     */
    public function setEnableTagRouter($enableTagRouter = true)
    {
        $this->enableTagRouter = (bool) $enableTagRouter;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
        );
    }

    /**
     * Replaces the default '_eztagsTag' route controller with legacy index controller
     * if set so in settings.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->attributes->get('_route') !== TagUrlGenerator::INTERNAL_TAG_ROUTE) {
            return;
        }

        if ($this->enableTagRouter) {
            return;
        }

        if ($this->logger instanceof LoggerInterface) {
            $this->logger->info('Falling back to tag legacy route as specified in config.');
        }

        $request->attributes->set('_route', FallbackRouter::ROUTE_NAME);
        $request->attributes->set('_controller', 'ezpublish_legacy.controller:indexAction');
    }
}
