<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\View\ParametersInjector;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use Netgen\TagsBundle\Core\Pagination\Pagerfanta\TagAdapterInterface;
use Netgen\TagsBundle\View\TagView;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RelatedContentPager implements EventSubscriberInterface
{
    /**
     * @var \Pagerfanta\Adapter\AdapterInterface
     */
    private $adapter;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    public function __construct(AdapterInterface $adapter, ConfigResolverInterface $configResolver)
    {
        $this->adapter = $adapter;
        $this->configResolver = $configResolver;
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

        $pagerLimit = $this->configResolver->getParameter('tag_view.related_content_list.limit', 'eztags');

        $pager->setMaxPerPage($pagerLimit > 0 ? $pagerLimit : 10);
        $pager->setCurrentPage($builderParameters['page'] > 0 ? $builderParameters['page'] : 1);

        $event->getParameterBag()->set('related_content', $pager);
    }
}
