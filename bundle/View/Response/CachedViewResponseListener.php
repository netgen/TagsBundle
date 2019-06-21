<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\View\Response;

use Netgen\TagsBundle\View\CacheableView;
use Netgen\TagsBundle\View\TagView;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CachedViewResponseListener implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $enableViewCache;

    /**
     * @var bool
     */
    private $enableTtlCache;

    /**
     * @var int
     */
    private $defaultTtl;

    public function __construct(bool $enableViewCache, bool $enableTtlCache, int $defaultTtl)
    {
        $this->enableViewCache = $enableViewCache;
        $this->enableTtlCache = $enableTtlCache;
        $this->defaultTtl = $defaultTtl;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => ['configureCache', 10]];
    }

    /**
     * Configures the caching headers on tag view.
     */
    public function configureCache(ResponseEvent $event): void
    {
        $view = $event->getRequest()->attributes->get('view');
        if (!$view instanceof CacheableView || !$view instanceof TagView) {
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
    }
}
