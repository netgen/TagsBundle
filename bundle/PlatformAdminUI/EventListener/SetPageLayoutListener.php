<?php

namespace Netgen\TagsBundle\PlatformAdminUI\EventListener;

use Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SetPageLayoutListener implements EventSubscriberInterface
{
    /**
     * @var \Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable
     */
    private $globalVariable;

    /**
     * @var string
     */
    private $pageLayoutTemplate;

    /**
     * @param \Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable $globalVariable
     * @param string $pageLayoutTemplate
     */
    public function __construct(AdminGlobalVariable $globalVariable, $pageLayoutTemplate)
    {
        $this->globalVariable = $globalVariable;
        $this->pageLayoutTemplate = $pageLayoutTemplate;
    }

    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => 'onKernelRequest');
    }

    /**
     * Sets the pagelayout template for admin interface.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $siteAccess = $event->getRequest()->attributes->get('siteaccess');
        if ($siteAccess->name !== 'admin') {
            return;
        }

        $this->globalVariable->setPageLayoutTemplate($this->pageLayoutTemplate);
    }
}
