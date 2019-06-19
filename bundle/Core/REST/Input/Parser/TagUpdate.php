<?php

namespace Netgen\TagsBundle\Core\REST\Input\Parser;

use EzSystems\EzPlatformRest\Exceptions;
use EzSystems\EzPlatformRest\Input\BaseParser;
use EzSystems\EzPlatformRest\Input\ParserTools;
use EzSystems\EzPlatformRest\Input\ParsingDispatcher;
use Netgen\TagsBundle\API\Repository\TagsService;

class TagUpdate extends BaseParser
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var \EzSystems\EzPlatformRest\Input\ParserTools
     */
    private $parserTools;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \EzSystems\EzPlatformRest\Input\ParserTools $parserTools
     */
    public function __construct(TagsService $tagsService, ParserTools $parserTools)
    {
        $this->tagsService = $tagsService;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRest\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \EzSystems\EzPlatformRest\Exceptions\Parser If the parsing failed
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $tagUpdateStruct = $this->tagsService->newTagUpdateStruct();

        if (array_key_exists('mainLanguageCode', $data)) {
            $tagUpdateStruct->mainLanguageCode = $data['mainLanguageCode'];
        }

        if (array_key_exists('remoteId', $data)) {
            $tagUpdateStruct->remoteId = $data['remoteId'];
        }

        if (array_key_exists('alwaysAvailable', $data)) {
            $tagUpdateStruct->alwaysAvailable = $this->parserTools->parseBooleanValue($data['alwaysAvailable']);
        }

        if (array_key_exists('names', $data)) {
            if (!is_array($data['names']) || !array_key_exists('value', $data['names']) || !is_array($data['names']['value'])) {
                throw new Exceptions\Parser("Invalid 'names' element for TagUpdate.");
            }

            $keywords = $this->parserTools->parseTranslatableList($data['names']);
            foreach ($keywords as $languageCode => $keyword) {
                $tagUpdateStruct->setKeyword($keyword, $languageCode);
            }
        }

        return $tagUpdateStruct;
    }
}
