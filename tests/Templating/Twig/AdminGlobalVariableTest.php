<?php

namespace Netgen\TagsBundle\Tests\Templating\Twig;

use Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable;
use PHPUnit\Framework\TestCase;

class AdminGlobalVariableTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable
     */
    protected $adminGlobalVariable;

    /**
     * @var string
     */
    protected $template;

    public function setUp()
    {
        $this->adminGlobalVariable = new AdminGlobalVariable();
        $this->template = '@Acme/pagelayout.html.twig';
    }

    public function testInstanceOfGlobalAdminVariable()
    {
        self::assertInstanceOf(AdminGlobalVariable::class, $this->adminGlobalVariable);
    }

    public function testGetAndSetPageLayoutTemplate()
    {
        self::assertNull($this->adminGlobalVariable->getPageLayoutTemplate());

        $this->adminGlobalVariable->setPageLayoutTemplate($this->template);
        self::assertEquals($this->template, $this->adminGlobalVariable->getPageLayoutTemplate());

        $this->adminGlobalVariable->setPageLayoutTemplate();
        self::assertNull($this->adminGlobalVariable->getPageLayoutTemplate());
    }
}
