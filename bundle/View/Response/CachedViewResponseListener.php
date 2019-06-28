<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\View\Response;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use FOS\HttpCache\ResponseTagger;
use Netgen\TagsBundle\View\CacheableView;
use Netgen\TagsBundle\View\TagView;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class CachedViewResponseListener implements EventSubscriberInterface
{
    /**
     * @var \FOS\HttpCache\ResponseTagger
     */
    private $responseTagger;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    public function __construct(ResponseTagger $responseTagger, ConfigResolverInterface $configResolver)
    {
        $this->responseTagger = $responseTagger;
        $this->configResolver = $configResolver;
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

        $cacheEnabled = (bool) $this->configResolver->getParameter('tag_view.cache', 'eztags');
        if (!$cacheEnabled || !$view->isCacheEnabled()) {
            return;
        }

        $tag = $view->getTag();
        $response = $event->getResponse();

        $response->setPublic();
        $this->responseTagger->addTags(['ngtags-tag-' . $tag->id]);

        $ttlCacheEnabled = (bool) $this->configResolver->getParameter('tag_view.ttl_cache', 'eztags');
        if ($ttlCacheEnabled && !$response->headers->hasCacheControlDirective('s-maxage')) {
            $response->setSharedMaxAge($this->configResolver->getParameter('tag_view.default_ttl', 'eztags'));
        }
    }
}
