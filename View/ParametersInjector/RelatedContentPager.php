<?php

namespace Netgen\TagsBundle\View\ParametersInjector;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Core\Pagination\Pagerfanta\TagAdapterInterface;
use Netgen\TagsBundle\View\TagView;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;

class RelatedContentPager implements EventSubscriberInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \Pagerfanta\Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * @var int
     */
    protected $pagerLimit;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \Pagerfanta\Adapter\AdapterInterface $adapter
     */
    public function __construct(TagsService $tagsService, AdapterInterface $adapter)
    {
        $this->tagsService = $tagsService;
        $this->adapter = $adapter;
    }

    /**
     * Sets the pager limit.
     *
     * @param int $pagerLimit
     */
    public function setPagerLimit($pagerLimit)
    {
        $this->pagerLimit = (int)$pagerLimit;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(ViewEvents::FILTER_VIEW_PARAMETERS => 'injectPager');
    }

    /**
     * Injects the pager with related content into the view.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent $event
     */
    public function injectPager(FilterViewParametersEvent $event)
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

        $pager->setMaxPerPage($this->pagerLimit > 0 ? $this->pagerLimit : 10);
        $pager->setCurrentPage($builderParameters['page'] > 0 ? $builderParameters['page'] : 1);

        $event->getParameterBag()->set('related_content', $pager);

        // Setting the pager variable too for BC
        // @deprecated since 2.1
        $event->getParameterBag()->set('pager', $pager);
    }
}
