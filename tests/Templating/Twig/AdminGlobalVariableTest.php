<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Templating\Twig;

use Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable;
use PHPUnit\Framework\TestCase;

class AdminGlobalVariableTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable
     */
    private $adminGlobalVariable;

    /**
     * @var string
     */
    private $template;

    protected function setUp(): void
    {
        $this->adminGlobalVariable = new AdminGlobalVariable();
        $this->template = '@Acme/pagelayout.html.twig';
    }

    public function testInstanceOfGlobalAdminVariable(): void
    {
        self::assertInstanceOf(AdminGlobalVariable::class, $this->adminGlobalVariable);
    }

    public function testGetAndSetPageLayoutTemplate(): void
    {
        self::assertNull($this->adminGlobalVariable->getPageLayoutTemplate());

        $this->adminGlobalVariable->setPageLayoutTemplate($this->template);
        self::assertSame($this->template, $this->adminGlobalVariable->getPageLayoutTemplate());

        $this->adminGlobalVariable->setPageLayoutTemplate();
        self::assertNull($this->adminGlobalVariable->getPageLayoutTemplate());
    }
}
