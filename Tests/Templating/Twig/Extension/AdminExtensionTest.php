<?php

namespace Netgen\TagsBundle\Tests\Templating\Twig\Extension;

use Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable;
use Netgen\TagsBundle\Templating\Twig\Extension\AdminExtension;
use PHPUnit_Framework_TestCase;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;

class AdminExtensionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Netgen\TagsBundle\Templating\Twig\Extension\AdminExtension
     */
    protected $extension;

    /**
     * @var \Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable
     */
    protected $adminGlobalVariable;

    public function setUp()
    {
        $this->adminGlobalVariable = new AdminGlobalVariable();
        $this->extension = new AdminExtension($this->adminGlobalVariable);
    }

    public function testInstanceOfTwigExtension()
    {
        $this->assertInstanceOf(Twig_Extension::class, $this->extension);
    }

    public function testInstanceOfTwigExtensionGlobalsInterface()
    {
        $this->assertInstanceOf(Twig_Extension_GlobalsInterface::class, $this->extension);
    }

    public function testGetName()
    {
        $this->assertEquals('Netgen\TagsBundle\Templating\Twig\Extension\AdminExtension', $this->extension->getName());
    }

    public function testGetGlobals()
    {
        $this->assertEquals(array('eztags_admin' => $this->adminGlobalVariable), $this->extension->getGlobals());
    }
}
