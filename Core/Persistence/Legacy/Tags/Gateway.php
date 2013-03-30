<?php

namespace EzSystems\TagsBundle\Core\Persistence\Legacy\Tags;

abstract class Gateway
{
    /**
     * Returns an array with basic tag data
     *
     * @param mixed $tagId
     *
     * @return array
     */
    abstract function getBasicTagData( $tagId );
}
