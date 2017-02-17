<?php

namespace Netgen\TagsBundle\Templating\Twig\Extension;

use Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;

class AdminExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    /**
     * @var \Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable
     */
    protected $globalVariable;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable $globalVariable
     */
    public function __construct(AdminGlobalVariable $globalVariable)
    {
        $this->globalVariable = $globalVariable;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::class;
    }

    /**
     * Returns a list of global variables to add to the existing list.
     *
     * @return array
     */
    public function getGlobals()
    {
        return array(
            'eztags_admin' => $this->globalVariable,
        );
    }
}
