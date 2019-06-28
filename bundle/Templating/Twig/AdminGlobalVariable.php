<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Templating\Twig;

use eZ\Publish\Core\MVC\ConfigResolverInterface;

final class AdminGlobalVariable
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var string
     */
    private $pageLayoutTemplate;

    /**
     * @var bool
     */
    private $isDefault = true;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function setPageLayoutTemplate(string $pageLayoutTemplate): void
    {
        $this->pageLayoutTemplate = $pageLayoutTemplate;
        $this->isDefault = false;
    }

    public function getPageLayoutTemplate(): string
    {
        if ($this->isDefault) {
            $this->pageLayoutTemplate = $this->configResolver->getParameter('admin.pagelayout', 'eztags');
        }

        return $this->pageLayoutTemplate;
    }
}
