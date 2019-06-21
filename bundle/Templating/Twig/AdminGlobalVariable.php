<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Templating\Twig;

final class AdminGlobalVariable
{
    /**
     * @var string
     */
    private $pageLayoutTemplate;

    public function setPageLayoutTemplate(?string $pageLayoutTemplate = null): void
    {
        $this->pageLayoutTemplate = $pageLayoutTemplate;
    }

    public function getPageLayoutTemplate(): ?string
    {
        return $this->pageLayoutTemplate;
    }
}
