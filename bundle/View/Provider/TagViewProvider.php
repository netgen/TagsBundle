<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\View\Provider;

use eZ\Publish\Core\MVC\Symfony\Matcher\MatcherFactoryInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\View\View;
use eZ\Publish\Core\MVC\Symfony\View\ViewProvider;
use Netgen\TagsBundle\View\TagView;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

final class TagViewProvider implements ViewProvider, SiteAccessAware
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherFactoryInterface
     */
    private $matcherFactory;

    public function __construct(MatcherFactoryInterface $matcherFactory)
    {
        $this->matcherFactory = $matcherFactory;
    }

    public function getView(View $view): View
    {
        if (($configHash = $this->matcherFactory->match($view)) === null) {
            return null;
        }

        return $this->buildTagView($configHash);
    }

    public function setSiteAccess(?SiteAccess $siteAccess = null): void
    {
        if ($this->matcherFactory instanceof SiteAccessAware) {
            $this->matcherFactory->setSiteAccess($siteAccess);
        }
    }

    /**
     * Builds a TagView object from $viewConfig.
     */
    private function buildTagView(array $viewConfig): TagView
    {
        $view = new TagView();
        $view->setConfigHash($viewConfig);

        if (isset($viewConfig['template'])) {
            $view->setTemplateIdentifier($viewConfig['template']);
        }

        if (isset($viewConfig['controller'])) {
            $view->setControllerReference(new ControllerReference($viewConfig['controller']));
        }

        return $view;
    }
}
