<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Templating\Twig\Extension;

use Netgen\TagsBundle\Templating\Twig\Extension\NetgenTagsExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

final class NetgenTagsExtensionTest extends TestCase
{
    private NetgenTagsExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new NetgenTagsExtension();
    }

    public function testGetFunctions(): void
    {
        self::assertContainsOnlyInstancesOf(TwigFunction::class, $this->extension->getFunctions());
    }
}
