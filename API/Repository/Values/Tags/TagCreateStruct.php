<?php

namespace Netgen\TagsBundle\API\Repository\Values\Tags;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a value for creating a tag.
 */
class TagCreateStruct extends ValueObject
{
    /**
     * The ID of the parent tag under which the new tag should be created.
     *
     * @required
     *
     * @var mixed
     */
    public $parentTagId;

    /**
     * The main language code for the tag.
     *
     * @required
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * Tag keywords in the target languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" ).
     *
     * @required
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
     * Indicates if the tag is shown in the main language if it's not present in an other requested language.
     *
     * @var bool
     */
    public $alwaysAvailable = true;

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
