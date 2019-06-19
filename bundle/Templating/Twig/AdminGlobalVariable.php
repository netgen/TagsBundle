<?php

namespace Netgen\TagsBundle\Templating\Twig;

class AdminGlobalVariable
{
    /**
     * @var string
     */
    protected $pageLayoutTemplate;

    /**
     * Sets the pagelayout template.
     */
    public function setPageLayoutTemplate(?string $pageLayoutTemplate = null): void
    {
        $this->pageLayoutTemplate = $pageLayoutTemplate;
    }

    /**
     * Returns the pagelayout template.
     */
    public function getPageLayoutTemplate(): ?string
    {
        return $this->pageLayoutTemplate;
    }
}
