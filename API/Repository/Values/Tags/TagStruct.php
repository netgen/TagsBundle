<?php

namespace Netgen\TagsBundle\API\Repository\Values\Tags;

use eZ\Publish\API\Repository\Values\ValueObject;

abstract class TagStruct extends ValueObject
{
    /**
     * The main language code for the tag.
     *
     * Required when creating a tag.
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * Tag keywords in the target languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" ).
     *
     * Required when creating a tag.
     *
     * @var string[]
     */
    protected $keywords = array();

    /**
     * A global unique ID of the tag.
     *
     * @var string
     */
    public $remoteId;

    /**
     * Returns keywords available in the struct.
     *
     * @return string[]
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Gets a keyword from keyword collection.
     *
     * @param string $language If not given, the main language is used
     *
     * @return string
     */
    public function getKeyword($language = null)
    {
        if (empty($language)) {
            $language = $this->mainLanguageCode;
        }

        if (!isset($this->keywords[$language])) {
            return null;
        }

        return $this->keywords[$language];
    }

    /**
     * Adds a keyword to keyword collection.
     *
     * @param string $keyword Keyword to add
     * @param string $language If not given, the main language is used
     */
    public function setKeyword($keyword, $language = null)
    {
        if (empty($language)) {
            $language = $this->mainLanguageCode;
        }

        $this->keywords[$language] = $keyword;
    }
}
