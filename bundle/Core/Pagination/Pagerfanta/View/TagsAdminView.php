<?php

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\View\ViewInterface;
use Twig\Environment;

class TagsAdminView implements ViewInterface
{
    /**
     * @var \Twig\Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var \Pagerfanta\Pagerfanta
     */
    protected $pagerfanta;

    /**
     * @var \Closure
     */
    protected $routeGenerator;

    /**
     * @var int
     */
    protected $proximity;

    /**
     * @var int
     */
    protected $startPage;

    /**
     * @var
     */
    protected $endPage;

    /**
     * Constructor.
     *
     * @param \Twig\Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Sets the default template.
     *
     * @param string $template
     */
    public function setDefaultTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Returns the canonical name.
     *
     * @return string
     */
    public function getName()
    {
        return 'eztags_admin';
    }

    /**
     * Renders a Pagerfanta.
     *
     * The route generator can be any callable to generate
     * the routes receiving the page number as first and
     * unique argument.
     *
     * @param \Pagerfanta\PagerfantaInterface $pagerfanta A pagerfanta
     * @param \Closure $routeGenerator A callable to generate the routes
     * @param array $options An array of options (optional)
     *
     * @return string
     */
    public function render(PagerfantaInterface $pagerfanta, $routeGenerator, array $options = [])
    {
        $this->pagerfanta = $pagerfanta;
        $this->routeGenerator = $routeGenerator;

        $this->initializeProximity($options);
        $this->calculateStartAndEndPage();

        return $this->twig->render(
            isset($options['template']) ? $options['template'] : $this->template,
            [
                'pager' => $pagerfanta,
                'pages' => $this->getPages(),
            ]
        );
    }

    /**
     * Initializes the proximity.
     *
     * @param array $options
     */
    protected function initializeProximity($options)
    {
        $this->proximity = isset($options['proximity']) ?
            (int) $options['proximity'] :
            2;
    }

    /**
     * Calculates start and end page that will be shown in the middle of pager.
     */
    protected function calculateStartAndEndPage()
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

        $this->startPage = (int) $startPage;
        $this->endPage = (int) $endPage;
    }

    /**
     * Calculates the end page when start page is underflowed.
     *
     * @param int $startPage
     * @param int $endPage
     * @param int $nbPages
     *
     * @return int
     */
    protected function calculateEndPageForStartPageUnderflow($startPage, $endPage, $nbPages)
    {
        return min($endPage + (1 - $startPage), $nbPages);
    }

    /**
     * Calculates the start page when end page is overflowed.
     *
     * @param int $startPage
     * @param int $endPage
     * @param int $nbPages
     *
     * @return int
     */
    protected function calculateStartPageForEndPageOverflow($startPage, $endPage, $nbPages)
    {
        return max($startPage - ($endPage - $nbPages), 1);
    }

    /**
     * Returns the list of all pages that need to be displayed.
     *
     * @return array
     */
    protected function getPages()
    {
        $pages = [];

        $pages['previous_page'] = $this->pagerfanta->hasPreviousPage() ?
            $this->generateUrl($this->pagerfanta->getPreviousPage()) :
            false;

        $pages['first_page'] = $this->startPage > 1 ? $this->generateUrl(1) : false;
        $pages['mobile_first_page'] = $this->pagerfanta->getCurrentPage() > 2 ? $this->generateUrl(1) : false;

        $pages['second_page'] = $this->startPage === 3 ? $this->generateUrl(2) : false;

        $pages['separator_before'] = $this->startPage > 3 ? true : false;

        $middlePages = [];
        for ($i = $this->startPage, $end = $this->endPage; $i <= $end; ++$i) {
            $middlePages[$i] = $this->generateUrl($i);
        }

        $pages['middle_pages'] = $middlePages;

        $pages['separator_after'] = $this->endPage < $this->pagerfanta->getNbPages() - 2 ? true : false;

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
     *
     * @param int $page
     *
     * @return string
     */
    protected function generateUrl($page)
    {
        $routeGenerator = $this->routeGenerator;

        // We use trim here because Pagerfanta (or Symfony?) adds an extra '?'
        // at the end of page when there are no other query params
        return trim($routeGenerator($page), '?');
    }
}
