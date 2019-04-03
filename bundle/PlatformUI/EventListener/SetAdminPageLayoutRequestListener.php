<?php

namespace Netgen\TagsBundle\PlatformUI\EventListener;

use Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SetAdminPageLayoutRequestListener extends PlatformUIListener implements EventSubscriberInterface
{
    /**
     * @var \Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable
     */
    protected $globalVariable;

    /**
     * @var string
     */
    protected $pageLayoutTemplate;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable $globalVariable
     * @param string $pageLayoutTemplate
     */
    public function __construct(AdminGlobalVariable $globalVariable, $pageLayoutTemplate)
    {
        $this->globalVariable = $globalVariable;
        $this->pageLayoutTemplate = $pageLayoutTemplate;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }

    /**
     * Sets the Netgen Tags admin pagelayout for eZ Platform UI.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$this->isPlatformUIRequest($event->getRequest())) {
            return;
        }

        $this->globalVariable->setPageLayoutTemplate($this->pageLayoutTemplate);
    }
}
