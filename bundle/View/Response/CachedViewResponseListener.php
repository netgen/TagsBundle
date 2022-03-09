<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\View\Response;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\TagsBundle\View\CacheableView;
use Netgen\TagsBundle\View\TagView;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class CachedViewResponseListener implements EventSubscriberInterface
{
    private ResponseTagger $responseTagger;

    private ConfigResolverInterface $configResolver;

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

        $cacheEnabled = (bool) $this->configResolver->getParameter('tag_view.cache', 'netgen_tags');
        if (!$cacheEnabled || !$view->isCacheEnabled()) {
            return;
        }

        $tag = $view->getTag();
        $response = $event->getResponse();

        $response->setPublic();
        $this->responseTagger->addTags(['ngtags-tag-' . $tag->id]);

        $ttlCacheEnabled = (bool) $this->configResolver->getParameter('tag_view.ttl_cache', 'netgen_tags');
        if ($ttlCacheEnabled && !$response->headers->hasCacheControlDirective('s-maxage')) {
            $response->setSharedMaxAge($this->configResolver->getParameter('tag_view.default_ttl', 'netgen_tags'));
        }
    }
}
