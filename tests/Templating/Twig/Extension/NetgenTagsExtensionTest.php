<?php

namespace Netgen\TagsBundle\Tests\Templating\Twig\Extension;

use Netgen\TagsBundle\Templating\Twig\Extension\NetgenTagsExtension;
use PHPUnit\Framework\TestCase;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NetgenTagsExtensionTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\Templating\Twig\Extension\NetgenTagsExtension
     */
    protected $extension;

    public function setUp()
    {
        $this->extension = new NetgenTagsExtension();
    }

    public function testInstanceOfTwigExtension()
    {
        $this->assertInstanceOf(AbstractExtension::class, $this->extension);
    }

    public function testGetFunctions()
    {
        foreach ($this->extension->getFunctions() as $function) {
            $this->assertInstanceOf(TwigFunction::class, $function);
        }
    }
}
