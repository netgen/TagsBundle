<?php

namespace EzSystems\TagsBundle\Core\FieldType\Tags\Value;

/**
 * Class representing a tag
 */
class Tag
{
    /**
     * Tag ID
     *
     * @var integer
     */
    protected $tagId;

    /**
     * Tag keyword
     *
     * @var string
     */
    protected $keyword;

    /**
     * Parent tag
     *
     * @var \EzSystems\TagsBundle\Core\FieldType\Tags\Value\Tag
     */
    protected $parentTag;

    /**
     * Constructor
     *
     * If the tag has no ID (i.e., not persisted yet to the storage), it should be set to null
     * If tag has no parent, $parentTag argument should be set to null
     *
     * @param integer $tagId Tag ID
     * @param string $keyword Tag keyword
     * @param \EzSystems\TagsBundle\Core\FieldType\Tags\Value\Tag $parentTag Parent tag
     */
    public function __construct( $tagId, $keyword, Tag $parentTag = null )
    {
        $this->tagId = $tagId;
        $this->keyword = $keyword;
        $this->parentTag = $parentTag;
    }

    /**
     * Returns the tag ID
     *
     * @return integer
     */
    public function getTagId()
    {
        return $this->tagId;
    }

    /**
     * Returns the tag keyword
     *
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Returns the parent tag
     *
     * @return \EzSystems\TagsBundle\Core\FieldType\Tags\Value\Tag
     */
    public function getParentTag()
    {
        return $this->parentTag;
    }
}
