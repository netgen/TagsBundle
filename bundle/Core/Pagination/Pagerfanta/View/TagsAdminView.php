<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\View\ViewInterface;
use Twig\Environment;

/**
 * @final
 */
class TagsAdminView implements ViewInterface
{
    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $template;

    /**
     * @var \Pagerfanta\Pagerfanta
     */
    private $pagerfanta;

    /**
     * @var callable
     */
    private $routeGenerator;

    /**
     * @var int
     */
    private $proximity;

    /**
     * @var int
     */
    private $startPage;

    /**
     * @var int
     */
    private $endPage;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function setDefaultTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function getName(): string
    {
        return 'eztags_admin';
    }

    /**
     * @param \Pagerfanta\Pagerfanta $pagerfanta
     * @param callable $routeGenerator
     * @param array $options
     */
    public function render(PagerfantaInterface $pagerfanta, $routeGenerator, array $options = []): string
    {
        $this->pagerfanta = $pagerfanta;
        $this->routeGenerator = $routeGenerator;

        $this->initializeProximity($options);
        $this->calculateStartAndEndPage();

        return $this->twig->render(
            $options['template'] ?? $this->template,
            [
                'pager' => $pagerfanta,
                'pages' => $this->getPages(),
            ]
        );
    }

    private function initializeProximity(array $options): void
    {
        $this->proximity = (int) ($options['proximity'] ?? 2);
    }

    /**
     * Calculates start and end page that will be shown in the middle of pager.
     */
    private function calculateStartAndEndPage(): void
    {
        $currentPage = $this->pagerfanta->getCurrentPage();
        $nbPages = $this->pagerfanta->getNbPages();

        $startPage = $currentPage - $this->proximity;
        $endPage = $currentPage + $this->proximity;

        if ($startPage < 1) {
            $endPage = $this->calculateEndPageForStartPageUnderflow($startPage, $endPage, $nbPages);
            $startPage = 1;
        }

        if ($endPage > $nbPages) {
            $startPage = $this->calculateStartPageForEndPageOverflow($startPage, $endPage, $nbPages);
            $endPage = $nbPages;
        }

        $this->startPage = $startPage;
        $this->endPage = $endPage;
    }

    /**
     * Calculates the end page when start page is underflowed.
     */
    private function calculateEndPageForStartPageUnderflow(int $startPage, int $endPage, int $nbPages): int
    {
        return min($endPage + (1 - $startPage), $nbPages);
    }

    /**
     * Calculates the start page when end page is overflowed.
     */
    private function calculateStartPageForEndPageOverflow(int $startPage, int $endPage, int $nbPages): int
    {
        return max($startPage - ($endPage - $nbPages), 1);
    }

    /**
     * Returns the list of all pages that need to be displayed.
     */
    private function getPages(): array
    {
        $pages = [];

        $pages['previous_page'] = $this->pagerfanta->hasPreviousPage() ?
            $this->generateUrl($this->pagerfanta->getPreviousPage()) :
            false;

        $pages['first_page'] = $this->startPage > 1 ? $this->generateUrl(1) : false;
        $pages['mobile_first_page'] = $this->pagerfanta->getCurrentPage() > 2 ? $this->generateUrl(1) : false;

        $pages['second_page'] = $this->startPage === 3 ? $this->generateUrl(2) : false;

        $pages['separator_before'] = $this->startPage > 3;

        $middlePages = [];
        for ($i = $this->startPage, $end = $this->endPage; $i <= $end; ++$i) {
            $middlePages[$i] = $this->generateUrl($i);
        }

        $pages['middle_pages'] = $middlePages;

        $pages['separator_after'] = $this->endPage < $this->pagerfanta->getNbPages() - 2;

        $pages['second_to_last_page'] = $this->endPage === $this->pagerfanta->getNbPages() - 2 ?
            $this->generateUrl($this->pagerfanta->getNbPages() - 1) :
            false;

        $pages['last_page'] = $this->pagerfanta->getNbPages() > $this->endPage ?
            $this->generateUrl($this->pagerfanta->getNbPages()) :
            false;

        $pages['mobile_last_page'] = $this->pagerfanta->getCurrentPage() < $this->pagerfanta->getNbPages() - 1 ?
            $this->generateUrl($this->pagerfanta->getNbPages()) :
            false;

        $pages['next_page'] = $this->pagerfanta->hasNextPage() ?
            $this->generateUrl($this->pagerfanta->getNextPage()) :
            false;

        return $pages;
    }

    /**
     * Generates the URL based on provided page.
     */
    private function generateUrl(int $page): string
    {
        $routeGenerator = $this->routeGenerator;

        // We use trim here because Pagerfanta (or Symfony?) adds an extra '?'
        // at the end of page when there are no other query params
        return trim($routeGenerator($page), '?');
    }
}
