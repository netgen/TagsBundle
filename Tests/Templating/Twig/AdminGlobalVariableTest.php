<?php

namespace Netgen\TagsBundle\Tests\Templating\Twig;

use Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable;
use PHPUnit_Framework_TestCase;

class AdminGlobalVariableTest extends PHPUnit_Framework_TestCase
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
        $this->template = 'AcmeBundle::pagelayout.html.twig';
    }

    public function testInstanceOfGlobalAdminVariable()
    {
        $this->assertInstanceOf(AdminGlobalVariable::class, $this->adminGlobalVariable);
    }

    public function testGetAndSetPageLayoutTemplate()
    {
        $this->assertNull($this->adminGlobalVariable->getPageLayoutTemplate());

        $this->adminGlobalVariable->setPageLayoutTemplate($this->template);
        $this->assertEquals($this->template, $this->adminGlobalVariable->getPageLayoutTemplate());

        $this->adminGlobalVariable->setPageLayoutTemplate();
        $this->assertNull($this->adminGlobalVariable->getPageLayoutTemplate());
    }
}
