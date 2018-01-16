<?php

namespace Netgen\TagsBundle\Templating\Twig\Extension;

use Twig\RuntimeLoader\RuntimeLoaderInterface;

class NetgenTagsRuntimeLoader implements RuntimeLoaderInterface
{
    /**
     * @var \Netgen\TagsBundle\Templating\Twig\Extension\NetgenTagsRuntime
     */
    protected $runtime;

    public function __construct(NetgenTagsRuntime $runtime)
    {
        $this->runtime = $runtime;
    }

    public function load($class)
    {
        if (!is_a($this->runtime, $class, true)) {
            return;
        }

        return $this->runtime;
    }
}
