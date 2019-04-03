<?php

namespace Netgen\TagsBundle\View\ParametersInjector;

use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use Netgen\TagsBundle\Core\Pagination\Pagerfanta\TagAdapterInterface;
use Netgen\TagsBundle\View\TagView;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RelatedContentPager implements EventSubscriberInterface
{
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
     * @param \Pagerfanta\Adapter\AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Sets the pager limit.
     *
     * @param int $pagerLimit
     */
    public function setPagerLimit($pagerLimit)
    {
        $this->pagerLimit = (int) $pagerLimit;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_VIEW_PARAMETERS => 'injectPager'];
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
    }
}
