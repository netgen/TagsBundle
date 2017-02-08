<?php

namespace Netgen\TagsBundle\View\Response;

use eZ\Publish\Core\MVC\Symfony\View\CachableView;
use Netgen\TagsBundle\View\TagView;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CachedViewResponseListener implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    protected $enableViewCache;

    /**
     * @var bool
     */
    protected $enableTtlCache;

    /**
     * @var int
     */
    protected $defaultTtl;

    /**
     * Constructor.
     *
     * @param bool $enableViewCache
     * @param bool $enableTtlCache
     * @param int $defaultTtl
     */
    public function __construct($enableViewCache, $enableTtlCache, $defaultTtl)
    {
        $this->enableViewCache = $enableViewCache;
        $this->enableTtlCache = $enableTtlCache;
        $this->defaultTtl = $defaultTtl;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::RESPONSE => array('configureCache', 10));
    }

    /**
     * Configures the caching headers on tag view.
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function configureCache(FilterResponseEvent $event)
    {
        $view = $event->getRequest()->attributes->get('view');
        if (!$view instanceof CachableView || !$view instanceof TagView) {
            return;
        }

        if (!$this->enableViewCache || !$view->isCacheEnabled()) {
            return;
        }

        $tag = $view->getTag();
        $response = $event->getResponse();

        $response->setPublic();
        $response->headers->set('X-Tag-Id', $tag->id, false);

        if ($this->enableTtlCache && !$response->headers->hasCacheControlDirective('s-maxage')) {
            $response->setSharedMaxAge($this->defaultTtl);
        }

        // We stop the propagation here so default cache view listener wouldn't mess with our view object.
        // All listeners that modify the cache for tag view object should run before this event
        $event->stopPropagation();
    }
}
