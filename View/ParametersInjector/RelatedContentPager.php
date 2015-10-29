<?php

namespace Netgen\TagsBundle\View\ParametersInjector;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Core\Pagination\Pagerfanta\RelatedContentAdapter;
use Netgen\TagsBundle\View\TagView;
use Pagerfanta\Pagerfanta;

class RelatedContentPager implements EventSubscriberInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var int
     */
    protected $pagerLimit;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
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

        $pager = new Pagerfanta(
            new RelatedContentAdapter($view->getTag(), $this->tagsService)
        );

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
