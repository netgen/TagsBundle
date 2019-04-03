<?php

namespace Netgen\TagsBundle\PlatformUI\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RedirectResponseListener extends PlatformUIListener implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => 'onKernelResponse'];
    }

    /**
     * Modifies the response to include PJAX-Location header for Platform UI.
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        if (!$event->isMasterRequest() || !$response->isRedirect()) {
            return;
        }

        if (!$this->isPlatformUIRequest($event->getRequest())) {
            return;
        }

        $response->setStatusCode(Response::HTTP_RESET_CONTENT);
        $response->headers->set('PJAX-Location', $response->headers->get('Location'));
        $response->headers->remove('Location');
    }
}
