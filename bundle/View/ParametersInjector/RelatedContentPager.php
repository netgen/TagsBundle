<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\View\ParametersInjector;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use Ibexa\Core\MVC\Symfony\View\ViewEvents;
use Netgen\TagsBundle\Core\Pagination\Pagerfanta\TagAdapterInterface;
use Netgen\TagsBundle\View\TagView;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RelatedContentPager implements EventSubscriberInterface
{
    public function __construct(private AdapterInterface $adapter, private ConfigResolverInterface $configResolver)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [ViewEvents::FILTER_VIEW_PARAMETERS => 'injectPager'];
    }

    /**
     * Injects the pager with related content into the view.
     */
    public function injectPager(FilterViewParametersEvent $event): void
    {
        $view = $event->getView();
        if (!$view instanceof TagView) {
            return;
        }

        if ($this->adapter instanceof TagAdapterInterface) {
            $this->adapter->setTag($view->getTag());
        }

        $pager = new Pagerfanta($this->adapter);
        $pager->setNormalizeOutOfRangePages(true);

        $builderParameters = $event->getBuilderParameters();

        $pagerLimit = $this->configResolver->getParameter('tag_view.related_content_list.limit', 'netgen_tags');

        $pager->setMaxPerPage($pagerLimit > 0 ? $pagerLimit : 10);
        $pager->setCurrentPage($builderParameters['page'] > 0 ? $builderParameters['page'] : 1);

        $event->getParameterBag()->set('related_content', $pager);
    }
}
