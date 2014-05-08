<?php

namespace Netgen\TagsBundle\Twig\Extension;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Twig_Extension;
use Twig_SimpleFunction;

class TagsExtension extends Twig_Extension
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * Constructor
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct( TagsService $tagsService )
    {
        $this->tagsService = $tagsService;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return "eztags";
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction(
                "eztags_tag_url",
                array( $this, "getTagUrl" ),
                array( "is_safe" => array( "html" ) )
            )
        );
    }

    public function getTagUrl( Tag $tag )
    {
        $url = urlencode( $tag->keyword );

        while ( $tag->parentTagId > 0 )
        {
            $tag = $this->tagsService->loadTag( $tag->parentTagId );
            $url = urlencode( $tag->keyword ) . "/" . $url;
        }

        return $url;
    }
}
