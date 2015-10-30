<?php

namespace Netgen\TagsBundle\Templating\Converter;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use eZ\Publish\Core\MVC\Legacy\Templating\Converter\ObjectConverter;
use InvalidArgumentException;
use Closure;
use eZTagsObject;

class TagConverter implements ObjectConverter
{
    /**
     * @var \Closure
     */
    protected $legacyKernel;

    /**
     * Constructor.
     *
     * @param \Closure $legacyKernel
     */
    public function __construct(Closure $legacyKernel = null)
    {
        $this->legacyKernel = $legacyKernel;
    }

    /**
     * Converts $object to make it compatible with legacy eZTemplate API.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $object
     *
     * @throws \InvalidArgumentException If $object is actually not an object
     *
     * @return \eZTagsObject
     */
    public function convert($object)
    {
        if (!$this->legacyKernel instanceof Closure) {
            return null;
        }

        if (!$object instanceof Tag) {
            throw new InvalidArgumentException('$object is not a Tag instance');
        }

        $legacyKernel = $this->legacyKernel;

        return $legacyKernel()->runCallback(
            function () use ($object) {
                return eZTagsObject::fetch($object->id);
            },
            false,
            false
        );
    }
}
