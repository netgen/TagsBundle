<?php

namespace Netgen\TagsBundle\PlatformAdminUI\EventListener;

use EzSystems\EzPlatformAdminUiBundle\EzPlatformAdminUiBundle;
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
     * @var array
     */
    private $groupsBySiteAccess;

    /**
     * @var string
     */
    private $pageLayoutTemplate;

    /**
     * @param \Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable $globalVariable
     * @param array $groupsBySiteAccess
     * @param string $pageLayoutTemplate
     */
    public function __construct(
        AdminGlobalVariable $globalVariable,
        array $groupsBySiteAccess,
        $pageLayoutTemplate
    ) {
        $this->globalVariable = $globalVariable;
        $this->groupsBySiteAccess = $groupsBySiteAccess;
        $this->pageLayoutTemplate = $pageLayoutTemplate;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 10]];
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

        $request = $event->getRequest();
        if (!$request->attributes->has('siteaccess')) {
            return;
        }

        $siteAccess = $request->attributes->get('siteaccess')->name;
        if (!isset($this->groupsBySiteAccess[$siteAccess])) {
            return;
        }

        if (!in_array(EzPlatformAdminUiBundle::ADMIN_GROUP_NAME, $this->groupsBySiteAccess[$siteAccess], true)) {
            return;
        }

        $this->globalVariable->setPageLayoutTemplate($this->pageLayoutTemplate);
    }
}
