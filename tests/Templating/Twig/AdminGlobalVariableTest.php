<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Templating\Twig;

use Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable;
use PHPUnit\Framework\TestCase;

final class AdminGlobalVariableTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable
     */
    private $adminGlobalVariable;

    protected function setUp(): void
    {
        $this->adminGlobalVariable = new AdminGlobalVariable();
    }

    public function testGetAndSetPageLayoutTemplate(): void
    {
        self::assertNull($this->adminGlobalVariable->getPageLayoutTemplate());

        $this->adminGlobalVariable->setPageLayoutTemplate('@Acme/pagelayout.html.twig');
        self::assertSame('@Acme/pagelayout.html.twig', $this->adminGlobalVariable->getPageLayoutTemplate());

        $this->adminGlobalVariable->setPageLayoutTemplate();
        self::assertNull($this->adminGlobalVariable->getPageLayoutTemplate());
    }
}
