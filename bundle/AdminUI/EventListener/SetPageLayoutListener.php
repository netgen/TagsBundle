<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\AdminUI\EventListener;

use Ibexa\Bundle\AdminUi\IbexaAdminUiBundle;
use Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use function in_array;

final class SetPageLayoutListener implements EventSubscriberInterface
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

    public function __construct(
        AdminGlobalVariable $globalVariable,
        array $groupsBySiteAccess,
        string $pageLayoutTemplate
    ) {
        $this->globalVariable = $globalVariable;
        $this->groupsBySiteAccess = $groupsBySiteAccess;
        $this->pageLayoutTemplate = $pageLayoutTemplate;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 10]];
    }

    /**
     * Sets the pagelayout template for admin interface.
     */
    public function onKernelRequest(RequestEvent $event): void
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

        if (!in_array(IbexaAdminUiBundle::ADMIN_GROUP_NAME, $this->groupsBySiteAccess[$siteAccess], true)) {
            return;
        }

        $this->globalVariable->setPageLayoutTemplate($this->pageLayoutTemplate);
    }
}
